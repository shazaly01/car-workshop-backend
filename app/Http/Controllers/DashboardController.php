<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        // 1. عدد السيارات قيد الصيانة
        // (الحالات التي تعتبر "قيد الصيانة" فعلياً)
        $activeMaintenanceCount = WorkOrder::whereIn('status', [
            'in_progress',
            'diagnosing',
            'pending_parts',
            'quality_check'
        ])->count();

        // 2. عدد الفواتير المعلقة (غير مدفوعة أو مدفوعة جزئياً)
        $pendingInvoicesCount = Invoice::whereIn('status', ['unpaid', 'partially_paid'])->count();

        // 3. إجمالي المبالغ المستحقة من الفواتير المعلقة
        $totalDueAmount = Invoice::whereIn('status', ['unpaid', 'partially_paid'])
                                 ->sum(DB::raw('total_amount - paid_amount'));

        // 4. أكثر 5 عملاء نشاطاً (بناءً على عدد أوامر العمل)
        $mostActiveClients = Client::withCount('workOrders') // يضيف عموداً وهمياً 'work_orders_count'
                                    ->orderBy('work_orders_count', 'desc')
                                    ->limit(5)
                                    ->get(['id', 'name', 'work_orders_count']);

        // 5. أحدث 5 أوامر عمل تم إنشاؤها
        $latestWorkOrders = WorkOrder::with(['client:id,name', 'vehicle:id,make,model'])
                                     ->latest()
                                     ->limit(5)
                                     ->get(['id', 'number', 'status', 'client_id', 'vehicle_id', 'created_at']);


        // تجميع كل البيانات في استجابة JSON واحدة
        return response()->json([
            'stats' => [
                'active_maintenance_count' => $activeMaintenanceCount,
                'pending_invoices_count' => $pendingInvoicesCount,
                'total_due_amount' => (float) $totalDueAmount,
            ],
            'most_active_clients' => $mostActiveClients,
            'latest_work_orders' => $latestWorkOrders,
        ]);
    }
}
