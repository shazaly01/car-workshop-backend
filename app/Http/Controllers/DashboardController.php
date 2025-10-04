<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * جلب جميع البيانات اللازمة لعرض لوحة التحكم.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // 1. إحصائية: عدد أوامر العمل قيد التنفيذ
        $workOrdersInProgressCount = WorkOrder::whereNotIn('status', ['completed', 'cancelled'])->count();

        // 2. إحصائية: عدد الفواتير المعلقة (غير مدفوعة)
        $pendingInvoicesCount = Invoice::whereNotIn('status', ['paid', 'voided'])->count();

        // 3. إحصائية: عدد أوامر العمل المكتملة اليوم
        $completedTodayCount = WorkOrder::where('status', 'completed')
            ->whereDate('updated_at', Carbon::today())
            ->count();

        // 4. قائمة: أحدث 5 أوامر عمل
        $latestWorkOrders = WorkOrder::with(['client', 'vehicle'])
            ->latest() // الترتيب حسب created_at تنازلياً
            ->take(5)
            ->get()
            ->map(function ($workOrder) {
                // إعادة هيكلة بسيطة لتسهيل العرض في الواجهة الأمامية
                return [
                    'id' => $workOrder->id,
                    'number' => $workOrder->number,
                    'status' => $workOrder->status,
                    'status_translated' => $this->getTranslatedStatus($workOrder->status),
                    'client_name' => $workOrder->client->name,
                    'vehicle_name' => $workOrder->vehicle->make . ' ' . $workOrder->vehicle->model,
                ];
            });

        // 5. قائمة: أكثر 5 عملاء نشاطاً
        $topClients = Client::withCount(['workOrders' => function ($query) {
                // يمكن إضافة فلتر زمني هنا إذا أردت، مثلاً لآخر 30 يوم
                // $query->where('created_at', '>=', Carbon::now()->subDays(30));
            }])
            ->orderBy('work_orders_count', 'desc')
            ->take(5)
            ->get()
            ->map(function ($client) {
                return [
                    'name' => $client->name,
                    'orders_count' => $client->work_orders_count,
                ];
            });

        // تجميع كل البيانات في استجابة واحدة
        return response()->json([
            'stats' => [
                'work_orders_in_progress' => $workOrdersInProgressCount,
                'pending_invoices' => $pendingInvoicesCount,
                'work_orders_completed_today' => $completedTodayCount,
            ],
            'latest_work_orders' => $latestWorkOrders,
            'top_clients' => $topClients,
        ]);
    }

    /**
     * دالة مساعدة لترجمة الحالات (مأخوذة من WorkOrderResource)
     */
    private function getTranslatedStatus(string $status): string
    {
        $statuses = [
            'pending_diagnosis' => 'بانتظار التشخيص',
            'diagnosing' => 'جاري التشخيص',
            'pending_quote_approval' => 'بانتظار موافقة عرض السعر',
            'quote_approved' => 'تمت الموافقة على عرض السعر',
            'quote_rejected' => 'تم رفض عرض السعر',
            'in_progress' => 'قيد الإصلاح',
            'pending_parts' => 'بانتظار قطع غيار',
            'quality_check' => 'فحص الجودة',
            'ready_for_delivery' => 'جاهز للتسليم',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
        ];

        return $statuses[$status] ?? $status;
    }
}
