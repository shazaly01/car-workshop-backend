<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreInvoiceRequest;

class InvoiceController extends Controller
{
    /**
     * Store a new invoice for a completed work order.
     *
     * @param \App\Http\Requests\StoreInvoiceRequest $request
     * @param \App\Models\WorkOrder $workOrder
     * @return \Illuminate\Http\JsonResponse|\App\Http\Resources\InvoiceResource
     */
   public function store(StoreInvoiceRequest $request, WorkOrder $workOrder): InvoiceResource | JsonResponse
    {
        // 1. التحقق من منطق العمل
        // التأكد من أن أمر العمل في حالة تسمح بالفوترة (مثلاً، جاهز للتسليم)
        if ($workOrder->status !== 'ready_for_delivery') {
            return response()->json(['message' => 'لا يمكن فوترة أمر العمل وهو ليس جاهزًا للتسليم.'], 409); // Conflict
        }

        // التأكد من عدم وجود فاتورة سابقة لهذا الأمر لمنع التكرار
        if ($workOrder->invoice()->exists()) {
            return response()->json(['message' => 'تم إصدار فاتورة لهذا الأمر بالفعل.'], 409);
        }

        // 2. جلب عرض السعر الموافق عليه (بافتراض وجود واحد فقط)
        // ملاحظة: يجب أن يكون لديك منطق عمل يضمن وجود عرض سعر واحد موافق عليه فقط
        $approvedQuotation = $workOrder->quotation()->where('status', 'approved')->first();

        if (!$approvedQuotation) {
            return response()->json(['message' => 'لا يوجد عرض سعر موافق عليه لإنشاء الفاتورة.'], 422); // Unprocessable Entity
        }

        // 3. استخدام Transaction لضمان سلامة البيانات
        $invoice = DB::transaction(function () use ($workOrder, $approvedQuotation) {

            // إنشاء الفاتورة (الرأس) بناءً على بيانات عرض السعر
            $invoice = $workOrder->invoice()->create([
                'client_id' => $workOrder->client_id,
                'number' => $this->generateNextInvoiceNumber(), // <-- استخدام رقم متسلسل
                'issue_date' => now(),
                'due_date' => now()->addDays(15), // تاريخ الاستحقاق بعد 15 يوم مثلاً
                'status' => 'unpaid', // الحالة الأولية
                'subtotal' => $approvedQuotation->subtotal,
                'tax_percentage' => 15.00, // يمكن جعله إعداداً عاماً
                'tax_amount' => $approvedQuotation->tax_amount,
                'total_amount' => $approvedQuotation->total_amount,
                'paid_amount' => 0,
            ]);

            // --- التغيير الرئيسي هنا ---
            // نسخ بنود عرض السعر إلى بنود الفاتورة مع الحفاظ على رابط الكتالوج
            $invoiceItems = [];
            foreach ($approvedQuotation->items as $quotationItem) {
                $invoiceItems[] = [
                    'catalog_item_id' => $quotationItem->catalog_item_id, // <-- نسخ رابط الكتالوج
                    'description' => $quotationItem->description,
                    'type' => $quotationItem->type,
                    'quantity' => $quotationItem->quantity,
                    'unit_price' => $quotationItem->unit_price,
                    'total_price' => $quotationItem->total_price,
                ];
            }
            $invoice->items()->createMany($invoiceItems);

            // يمكنك تحديث حالة أمر العمل هنا إذا أردت
            // $workOrder->status = 'completed';
            // $workOrder->save();
            // ملاحظة: قد يكون من الأفضل تغيير حالة أمر العمل إلى "مكتمل" بعد الدفع الكامل وليس فقط بعد إنشاء الفاتورة

            return $invoice;
        });

        // تحميل العلاقات اللازمة للاستجابة الكاملة
        $invoice->load(['client', 'items.catalogItem']);

        return new InvoiceResource($invoice);
    }

    /**
     * دالة مساعدة بسيطة لإنشاء رقم فاتورة متسلسل
     * @return string
     */
    private function generateNextInvoiceNumber(): string
    {
        $latestInvoice = Invoice::latest('id')->first();

        if (!$latestInvoice) {
            $number = 1;
        } else {
            $lastNumber = (int) substr($latestInvoice->number, strrpos($latestInvoice->number, '-') + 1);
            $number = $lastNumber + 1;
        }

        // INV-2025-0001
        return 'INV-' . date('Y') . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }



     /**
     * Void an existing invoice.
     *
     * @param \App\Models\Invoice $invoice
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Invoice $invoice): JsonResponse
    {
        // 1. التحقق من الصلاحية باستخدام الـ Policy
        $this->authorize('void', $invoice);

        // 2. التحقق من منطق العمل (هل الفاتورة قابلة للإلغاء؟)
        // بناءً على الـ Policy الذي قدمته سابقًا، هذا الشرط مكرر
        // لكن من الجيد إبقاؤه هنا لإرجاع رسالة خطأ واضحة.
        if (in_array($invoice->status, ['paid', 'partially_paid'])) {
            return response()->json(['message' => 'لا يمكن إلغاء فاتورة مدفوعة.'], 409); // Conflict
        }

        if ($invoice->status === 'voided') {
            return response()->json(['message' => 'هذه الفاتورة ملغاة بالفعل.'], 409);
        }

        // 3. تنفيذ الإلغاء
        $invoice->status = 'voided';
        $invoice->save();

        // 4. إرجاع استجابة ناجحة
        return response()->json(null, 204); // 204 No Content
    }
}
