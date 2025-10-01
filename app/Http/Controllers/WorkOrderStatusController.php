<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateWorkOrderStatusRequest;
use App\Http\Resources\WorkOrderResource;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class WorkOrderStatusController extends Controller
{
      use AuthorizesRequests;
    /**
     * Update the status of a specific work order.
     *
     * @param  \App\Http\Requests\UpdateWorkOrderStatusRequest  $request
     * @param  \App\Models\WorkOrder  $workOrder
     * @return \App\Http\Resources\WorkOrderResource
     */
    public function update(UpdateWorkOrderStatusRequest $request, WorkOrder $workOrder): WorkOrderResource
    {
         $this->authorize('changeStatus', $workOrder);
        // الحصول على الحالة الجديدة من الطلب الذي تم التحقق منه
        $newStatus = $request->validated('status');

        // --- منطق العمل الإضافي (اختياري ولكنه مهم) ---
        // يمكنك هنا إضافة قواعد لمنع الانتقال بين حالات معينة.
        // مثال: لا يمكن الانتقال إلى "مكتمل" إلا من "جاهز للتسليم".
        // if ($workOrder->status === 'ready_for_delivery' && $newStatus === 'completed') {
        //     // ... منطق إضافي مثل إنشاء الفاتورة تلقائياً
        // }

        // تحديث حقل الحالة فقط
        $workOrder->status = $newStatus;
        $workOrder->save();

        // --- إطلاق الأحداث (Events) ---
        // أفضل ممارسة: أطلق حدثاً عند تغيير الحالة.
        // يمكن للمستمعين (Listeners) لهذا الحدث القيام بمهام مثل إرسال إشعارات.
        // event(new WorkOrderStatusChanged($workOrder, $oldStatus));

        // إرجاع أمر العمل بعد تحديث حالته، منسقاً عبر المورد
        return new WorkOrderResource($workOrder);
    }
}
