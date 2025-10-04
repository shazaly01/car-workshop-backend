<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuotationRequest;
use App\Http\Requests\UpdateQuotationRequest;
use App\Http\Resources\QuotationResource;
use App\Http\Resources\WorkOrderResource;
use App\Models\CatalogItem;
use App\Models\Quotation;
use App\Models\WorkOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
                'status' => 'approved',
            ]);

            // 2. أنشئ البنود
            $quotation->items()->createMany($itemsToCreate);

            // 3. حدّث حالة أمر العمل إلى "in_progress" فورًا
            $workOrder->status = 'in_progress';
            $workOrder->save();

            return $quotation;
        });

        $quotation->load('items.catalogItem');
        return new QuotationResource($quotation);
    }

   // ... (داخل QuotationController.php)

  /**
     * Update the specified resource in storage.
     *
     * @param UpdateQuotationRequest $request
     * @param Quotation $quotation
     * @return WorkOrderResource|QuotationResource|JsonResponse
     */
   /**
     * تحديث عرض سعر موجود ومزامنة الفاتورة المرتبطة به تلقائياً.
     *
     * @param \App\Http\Requests\UpdateQuotationRequest $request
     * @param \App\Models\Quotation $quotation
     * @return \App\Http\Resources\QuotationResource
     */
   // في ملف: app/Http/Controllers/QuotationController.php

public function update(UpdateQuotationRequest $request, Quotation $quotation): QuotationResource
{
    $workOrder = $quotation->workOrder;
    $invoice = $workOrder->invoice;

    if ($invoice && $invoice->status === 'paid') {
        abort(403, 'لا يمكن تعديل عرض السعر لأن الفاتورة النهائية قد تم دفعها بالكامل.');
    }

    $validatedData = $request->validated();

    DB::transaction(function () use ($quotation, $validatedData, $invoice) {

        // --- [بداية الإصلاح] ---

        // 1. تحضير بيانات البنود الجديدة مع جلب الوصف من قاعدة البيانات
        $itemsToCreate = [];
        $catalogItemIds = array_column($validatedData['items'], 'catalog_item_id');

        // جلب كل بنود الكتالوج المطلوبة في استعلام واحد لتحسين الأداء
        $catalogItems = \App\Models\CatalogItem::whereIn('id', $catalogItemIds)->get()->keyBy('id');

        foreach ($validatedData['items'] as $itemData) {
            $catalogItem = $catalogItems->get($itemData['catalog_item_id']);
            if ($catalogItem) {
                $itemsToCreate[] = [
                    'catalog_item_id' => $itemData['catalog_item_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    // [الحل] إضافة الوصف والحقول الأخرى من الكتالوج مباشرة
                    'description' => $catalogItem->name,
                    'type' => $catalogItem->type,
                    'total_price' => $itemData['quantity'] * $itemData['unit_price'],
                ];
            }
        }

        // 2. تحديث عرض السعر
        $quotation->items()->delete();
        $quotation->items()->createMany($itemsToCreate); // <-- استخدام البيانات المحضرة والكاملة

        // --- [نهاية الإصلاح] ---

        $quotation->load('items');

        $subtotal = $quotation->items->sum('total_price');
        $tax = $subtotal * 0.15;
        $total = $subtotal + $tax;

        $quotation->update([
            'notes' => $validatedData['notes'] ?? $quotation->notes,
            'subtotal' => $subtotal,
            'tax_amount' => $tax,
            'total_amount' => $total,
        ]);

        // --- مزامنة الفاتورة (المنطق هنا يبقى كما هو) ---
        if ($invoice) {
            $oldInvoiceAmount = $invoice->total_amount;

            if ($invoice->paid_amount > 0) {
                $invoice->revisions()->create([
                    'user_id' => Auth::id(),
                    'old_amount' => $oldInvoiceAmount,
                    'new_amount' => $total,
                ]);
            }

            $invoice->update([
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'total_amount' => $total,
            ]);

            $invoice->items()->delete();
            // استخدام نفس البيانات المحضرة للفاتورة أيضًا
            $invoice->items()->createMany($itemsToCreate);
        }
    });

    return new QuotationResource($quotation->fresh(['items']));
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
