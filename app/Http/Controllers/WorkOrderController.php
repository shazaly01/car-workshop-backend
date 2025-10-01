<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkOrderRequest;
use App\Http\Requests\UpdateWorkOrderRequest;
use App\Http\Resources\WorkOrderResource;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str; // لاستخدامه في إنشاء رقم أمر العمل
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class WorkOrderController extends Controller
{

      use AuthorizesRequests;
    public function __construct()
{
    $this->authorizeResource(WorkOrder::class, 'work_order');
}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = WorkOrder::with(['client', 'vehicle'])->latest();

        // إضافة فلترة بسيطة بناءً على الحالة
        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        $workOrders = $query->paginate(20);

        return WorkOrderResource::collection($workOrders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWorkOrderRequest $request): WorkOrderResource
    {
        // دمج البيانات التي تم التحقق منها مع البيانات التي ننشئها في السيرفر
        $data = array_merge($request->validated(), [
            'number' => $this->generateWorkOrderNumber(),
            'status' => 'pending_diagnosis', // الحالة الأولية
            'created_by_user_id' =>  Auth::id(),
        ]);

        $workOrder = WorkOrder::create($data);

        // تحميل العلاقات لعرضها في الاستجابة
        $workOrder->load(['client', 'vehicle', 'createdBy']);

        return new WorkOrderResource($workOrder);
    }

    /**
     * Display the specified resource.
     */
    public function show(WorkOrder $workOrder): WorkOrderResource
    {
        // تحميل كل العلاقات المتعلقة بأمر العمل لعرض صفحة تفاصيل كاملة
        $workOrder->load([
            'client',
            'vehicle',
            'createdBy',
            'diagnosis',
            'quotation.items', // تحميل عروض الأسعار مع بنودها
            'invoice.items',    // تحميل الفاتورة مع بنودها
            'invoice.payments'  // تحميل مدفوعات الفاتورة
        ]);

        return new WorkOrderResource($workOrder);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWorkOrderRequest $request, WorkOrder $workOrder): WorkOrderResource
    {
        $workOrder->update($request->validated());

        // إعادة تحميل العلاقات الأساسية
        $workOrder->load(['client', 'vehicle', 'createdBy']);

        return new WorkOrderResource($workOrder);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WorkOrder $workOrder): Response | JsonResponse
    {
        // منطق إضافي: قد لا نسمح بحذف أمر عمل مكتمل
        if ($workOrder->status === 'completed') {
            return response()->json(['message' => 'لا يمكن حذف أمر عمل مكتمل.'], 403); // 403 Forbidden
        }

        $workOrder->delete();

        return response()->noContent();
    }

    /**
     * دالة مساعدة لإنشاء رقم أمر عمل فريد واحترافي
     */
    private function generateWorkOrderNumber(): string
{
    // --- هذا هو التعديل ---
    $nextNumber = ''; // أعطه قيمة نصية فارغة كقيمة أولية

    // استخدام Transaction لضمان سلامة العملية
    DB::transaction(function () use (&$nextNumber) {
        // 1. ابحث عن آخر أمر عمل وقم بقفل الجدول لمنع أي عملية أخرى
        $lastOrder = WorkOrder::orderBy('id', 'desc')->lockForUpdate()->first();

        if ($lastOrder && $lastOrder->number) {
            // 2. استخرج الرقم من آخر أمر عمل
            $parts = explode('-', $lastOrder->number);
            $lastSequentialNumber = (int) end($parts);
            $nextSequentialNumber = $lastSequentialNumber + 1;
        } else {
            // 3. إذا لم يكن هناك أوامر عمل، ابدأ من 1001
            $nextSequentialNumber = 1001;
        }

        $nextNumber = 'WO-' . date('Y') . '-' . $nextSequentialNumber;
    });

    return $nextNumber;
}

}
