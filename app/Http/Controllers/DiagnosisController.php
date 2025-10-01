<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDiagnosisRequest;
use App\Http\Resources\DiagnosisResource;
use App\Models\Diagnosis;
use App\Models\WorkOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // <-- 1. استيراد واجهة قاعدة البيانات
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DiagnosisController extends Controller
{
    use AuthorizesRequests;
    /**
     * Store a new diagnosis for a work order.
     *
     * @param \App\Http\Requests\StoreDiagnosisRequest $request
     * @param \App\Models\WorkOrder $workOrder
     * @return \App\Http\Resources\DiagnosisResource|\Illuminate\Http\JsonResponse
     */
    public function store(StoreDiagnosisRequest $request, WorkOrder $workOrder): DiagnosisResource | JsonResponse
    {

        $this->authorize('create', [Diagnosis::class, $workOrder]);
        // التحقق من أن أمر العمل في حالة تسمح بالتشخيص
        // ملاحظة: من الأفضل استخدام ثوابت مثل WorkOrder::STATUS_PENDING_DIAGNOSIS
        if (!in_array($workOrder->status, ['pending_diagnosis', 'diagnosing'])) {
            return response()->json([
                'message' => 'لا يمكن إضافة تشخيص لأمر العمل هذا لأنه ليس في حالة التشخيص.'
            ], 409); // 409 Conflict
        }

        // دمج البيانات مع ID أمر العمل والفني
        // ملاحظة: لم نعد بحاجة لـ work_order_id هنا لأننا سنستخدم علاقة Eloquent
        $validatedData = $request->validated();

        // 2. استخدام Transaction لضمان أن العملية كلها تنجح أو تفشل كوحدة واحدة
        $diagnosis = DB::transaction(function () use ($validatedData, $workOrder) {

            // 3. إنشاء التشخيص مرتبطًا مباشرة بأمر العمل
            $diagnosis = $workOrder->diagnosis()->create([
                'technician_id' => Auth::id(), // الفني هو المستخدم المسجل حالياً
                'obd_codes' => $validatedData['obd_codes'] ?? null,
                'manual_inspection_results' => $validatedData['manual_inspection_results'],
                'proposed_repairs' => $validatedData['proposed_repairs'] ?? null,
            ]);

            // 4. تحديث حالة أمر العمل إلى "بانتظار موافقة عرض السعر"
            $workOrder->status = 'pending_quote_approval'; // الأفضل: WorkOrder::STATUS_PENDING_QUOTE_APPROVAL
            $workOrder->save();

            // 5. إرجاع كائن التشخيص من الـ transaction لاستخدامه لاحقًا
            return $diagnosis;
        });

        // تحميل علاقة الفني لعرضها في الاستجابة
        $diagnosis->load('technician');

        return new DiagnosisResource($diagnosis);
    }
}
