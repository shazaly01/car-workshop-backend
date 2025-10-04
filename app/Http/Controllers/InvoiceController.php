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
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InvoiceController extends Controller
{


   /**
     * عرض قائمة بجميع الفواتير مع الفلترة والبحث.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        // ابدأ بالاستعلام مع تحميل علاقة العميل لتحسين الأداء
        $query = Invoice::with('client')->latest();

        // 1. الفلترة بالحالة (النسخة المحسّنة)
        if ($request->filled('status') && $request->status !== 'all') {
            $status = $request->status;

            if ($status === 'due') {
                // إذا طلبنا الفواتير "المستحقة"، فهي تشمل غير المدفوعة والمدفوعة جزئيًا
                $query->whereIn('status', ['unpaid', 'partially_paid']);
            } else {
                // لأي حالة أخرى، استخدمها مباشرة
                $query->where('status', 'like', $status);
            }
        }

        // 2. الفلترة بنطاق التاريخ (إذا تم توفيره)
        if ($request->filled('start_date')) {
            $query->whereDate('issue_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('issue_date', '<=', $request->end_date);
        }

        // 3. البحث (برقم الفاتورة أو اسم العميل)
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('number', 'like', "%{$searchTerm}%")
                  ->orWhereHas('client', function ($clientQuery) use ($searchTerm) {
                      $clientQuery->where('name', 'like', "%{$searchTerm}%");
                  });
            });
        }

        // تنفيذ الاستعلام مع الت分页 (Pagination)
        // withQueryString() مهمة للحفاظ على الفلاتر عند التنقل بين الصفحات
        $invoices = $query->paginate(15)->withQueryString();

        return InvoiceResource::collection($invoices);
    }


    /**
     * Store a new invoice for a completed work order.
     *
     * @param \App\Http\Requests\StoreInvoiceRequest $request
     * @param \App\Models\WorkOrder $workOrder
     * @return \Illuminate\Http\JsonResponse|\App\Http\Resources\InvoiceResource
     */
public function store(Request $request, WorkOrder $workOrder): InvoiceResource | JsonResponse
{
    // 1. التحقق من منطق العمل
    // التأكد من وجود عرض سعر أصلاً لإنشاء فاتورة منه
    $quotation = $workOrder->quotation;
    if (!$quotation) {
        return response()->json(['message' => 'لا يمكن إنشاء فاتورة لعدم وجود عرض سعر.'], 422);
    }

    // التأكد من عدم وجود فاتورة سابقة لهذا الأمر لمنع التكرار
    if ($workOrder->invoice()->exists()) {
        return response()->json(['message' => 'تم إصدار فاتورة لهذا الأمر بالفعل.'], 409);
    }

    // 2. استخدام Transaction لضمان سلامة البيانات
    $invoice = DB::transaction(function () use ($workOrder, $quotation) {

        // أ. إنشاء الفاتورة (الرأس) بناءً على بيانات عرض السعر
        $invoice = $workOrder->invoice()->create([
            'client_id' => $workOrder->client_id,
            'number' => $this->generateNextInvoiceNumber(),
            'issue_date' => now(),
            'due_date' => now()->addDays(15),
            'status' => 'unpaid', // الحالة الأولية
            'subtotal' => $quotation->subtotal,
            'tax_percentage' => 15.00,
            'tax_amount' => $quotation->tax_amount,
            'total_amount' => $quotation->total_amount,
            'paid_amount' => 0,
        ]);

        // ب. نسخ بنود عرض السعر إلى بنود الفاتورة
        $invoiceItems = [];
        foreach ($quotation->items as $quotationItem) {
            $invoiceItems[] = [
                'catalog_item_id' => $quotationItem->catalog_item_id,
                'description' => $quotationItem->description,
                'type' => $quotationItem->type,
                'quantity' => $quotationItem->quantity,
                'unit_price' => $quotationItem->unit_price,
                'total_price' => $quotationItem->total_price,
            ];
        }
        $invoice->items()->createMany($invoiceItems);

        // ج. [الخطوة الحاسمة] تحديث الحالات بعد إنشاء الفاتورة
        $quotation->update(['status' => 'approved']);
        $workOrder->update(['status' => 'in_progress']);

        return $invoice;
    });

  $workOrder->refresh()->load([
        'client',
        'vehicle',
        'diagnosis',
        'quotation',
        'invoice.items', // الأهم: تحميل الفاتورة الجديدة مع بنودها
        'invoice.payments'
    ]);

    // 2. إرجاع كائن أمر العمل المحدث بالكامل باستخدام الـ Resource الخاص به.
    return response()->json([
        'message' => 'تم إنشاء الفاتورة بنجاح.',
        'work_order' => new \App\Http\Resources\WorkOrderResource($workOrder) // <-- إرجاع أمر العمل
    ]);

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
     * [جديد] عرض تفاصيل فاتورة واحدة مع جميع علاقاتها.
     *
     * @param \App\Models\Invoice $invoice
     * @return \App\Http\Resources\InvoiceResource
     */
    public function show(Invoice $invoice): InvoiceResource
    {
        // تحميل جميع العلاقات التي نحتاجها في صفحة التفاصيل دفعة واحدة
        $invoice->load(['client', 'items', 'payments.receivedBy']);

        return new InvoiceResource($invoice);
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
