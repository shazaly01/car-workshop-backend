<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuotationRequest;
use App\Http\Requests\UpdateQuotationRequest;
use App\Http\Resources\QuotationResource;
use App\Models\CatalogItem;
use App\Models\Quotation;
use App\Models\WorkOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function store(StoreQuotationRequest $request, WorkOrder $workOrder): QuotationResource | JsonResponse
    {
        if ($workOrder->status !== 'pending_quote_approval') {
            return response()->json(['message' => 'لا يمكن إنشاء عرض سعر لأمر العمل هذا في حالته الحالية.'], 409);
        }

        // --- هذا هو التعديل: تحقق مما إذا كان هناك عرض سعر بالفعل ---
        if ($workOrder->quotation()->exists()) {
            return response()->json(['message' => 'يوجد عرض سعر بالفعل لأمر العمل هذا. يمكنك تعديله بدلاً من إنشاء واحد جديد.'], 409);
        }

        $validatedData = $request->validated();

        $quotation = DB::transaction(function () use ($validatedData, $workOrder) {
            $subtotal = 0;
            $itemsToCreate = [];

            // 1. جهز البنود واحسب المجموع
            foreach ($validatedData['items'] as $item) {
                $catalogItem = CatalogItem::find($item['catalog_item_id']);
                $unitPrice = $item['unit_price']; // استخدم السعر المرسل (للسماح بالتجاوز)
                $totalPrice = $item['quantity'] * $unitPrice;
                $subtotal += $totalPrice;

                $itemsToCreate[] = [
                    'catalog_item_id' => $catalogItem->id,
                    'description' => $catalogItem->name,
                    'type' => $catalogItem->type,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ];
            }

            $taxAmount = $subtotal * 0.15; // يمكن جعلها نسبة عامة
            $totalAmount = $subtotal + $taxAmount;

            // 2. أنشئ عرض السعر
            $quotation = $workOrder->quotation()->create([
                'number' => $this->generateNextQuotationNumber(), // <-- سيستخدم الدالة الجديدة
                'issue_date' => $validatedData['issue_date'],
                'expiry_date' => $validatedData['expiry_date'],
                'notes' => $validatedData['notes'] ?? null,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ]);

            // 3. أنشئ البنود
            $quotation->items()->createMany($itemsToCreate);

            return $quotation;
        });

        $quotation->load('items.catalogItem');
        return new QuotationResource($quotation);
    }

   // ... (داخل QuotationController.php)

  public function update(UpdateQuotationRequest $request, Quotation $quotation): QuotationResource | JsonResponse
    {
        $validatedData = $request->validated();

        // --- السيناريو الجديد: تحديث الحالة ---
        if (isset($validatedData['status'])) {
            // تحقق من أن عرض السعر في حالة تسمح بتغيير حالته
            if ($quotation->status !== 'pending') {
                return response()->json(['message' => 'لا يمكن تغيير حالة عرض السعر هذا لأنه ليس في حالة "معلق".'], 409);
            }

            DB::transaction(function () use ($quotation, $validatedData) {
                $quotation->status = $validatedData['status'];
                $quotation->save();

                // إذا تم الرفض، قم بإلغاء أمر العمل
                if ($validatedData['status'] === 'rejected') {
                    $quotation->workOrder()->update(['status' => 'cancelled']);
                }
                // إذا تمت الموافقة، قم بتغيير حالة أمر العمل
                elseif ($validatedData['status'] === 'approved') {
                    $quotation->workOrder()->update(['status' => 'in_progress']);
                }
            });

            $quotation->load('workOrder'); // أعد تحميل العلاقة المحدثة
            return new QuotationResource($quotation);
        }


        // --- السيناريو القديم: تحديث البنود ---
        if (isset($validatedData['items'])) {
            // تحقق من أن عرض السعر في حالة تسمح بالتعديل
            if ($quotation->status !== 'pending') {
                return response()->json(['message' => 'لا يمكن تعديل بنود عرض السعر هذا لأنه ليس في حالة "معلق".'], 409);
            }

            $updatedQuotation = DB::transaction(function () use ($validatedData, $quotation) {
                // ... (منطق تحديث البنود يبقى كما هو)
                $quotation->items()->delete();
                $subtotal = 0;
                $itemsToCreate = [];
                foreach ($validatedData['items'] as $item) {
                    $catalogItem = CatalogItem::find($item['catalog_item_id']);
                    $unitPrice = $item['unit_price'];
                    $totalPrice = $item['quantity'] * $unitPrice;
                    $subtotal += $totalPrice;
                    $itemsToCreate[] = [
                        'catalog_item_id' => $catalogItem->id, 'description' => $catalogItem->name,
                        'type' => $catalogItem->type, 'quantity' => $item['quantity'],
                        'unit_price' => $unitPrice, 'total_price' => $totalPrice,
                    ];
                }
                $taxAmount = $subtotal * 0.15;
                $totalAmount = $subtotal + $taxAmount;
                $quotation->update([
                    'notes' => $validatedData['notes'] ?? $quotation->notes,
                    'subtotal' => $subtotal, 'tax_amount' => $taxAmount, 'total_amount' => $totalAmount,
                ]);
                $quotation->items()->createMany($itemsToCreate);
                return $quotation;
            });

            $updatedQuotation->load('items.catalogItem');
            return new QuotationResource($updatedQuotation);
        }

        // في حالة إرسال طلب تحديث فارغ أو غير مدعوم
        return response()->json(['message' => 'طلب التحديث غير صالح.'], 400);
    }

// ... (بقية دوال المتحكم)

    /**
     * دالة مساعدة آمنة لإنشاء رقم عرض سعر متسلسل
     * @return string
     */
    private function generateNextQuotationNumber(): string
    {
        $nextNumber = '';

        DB::transaction(function () use (&$nextNumber) {
            // 1. اقفل آخر سجل في جدول عروض الأسعار لمنع أي عملية أخرى من الإنشاء في نفس الوقت
            $latestQuotation = Quotation::latest('id')->lockForUpdate()->first();

            if (!$latestQuotation) {
                $number = 1;
            } else {
                $lastNumber = (int) substr($latestQuotation->number, strrpos($latestQuotation->number, '-') + 1);
                $number = $lastNumber + 1;
            }

            // 2. قم بتنسيق الرقم الجديد
            $nextNumber = 'Q-' . date('Y') . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
        });

        return $nextNumber;
    }
}
