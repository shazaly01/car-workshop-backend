<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate; // *** أضف هذا السطر ***

class RevenueReportController extends Controller
{
    /**
     * جلب ملخص الإيرادات والإحصائيات.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRevenueSummary(Request $request): JsonResponse
    {
        // *** START: AUTHORIZATION CHECK ***
        // التحقق من الصلاحية باستخدام الـ Gate الذي تم تسجيله
        if (! Gate::allows('view-revenue-report')) {
            abort(403, 'This action is unauthorized.');
        }
        // *** END: AUTHORIZATION CHECK ***

        // 1. تحديد النطاق الزمني (افتراضيًا آخر 30 يومًا)
        $startDate = $request->input('start_date', now()->subDays(30)->startOfDay());
        $endDate = $request->input('end_date', now()->endOfDay());

        // 2. حساب إجمالي قيمة الفواتير الصادرة في الفترة المحددة (غير الملغاة)
        $totalInvoiced = Invoice::whereBetween('issue_date', [$startDate, $endDate])
            ->where('status', '!=', 'voided')
            ->sum('total_amount');

        // 3. حساب إجمالي المبالغ المدفوعة في الفترة المحددة
        $totalPaid = Payment::whereBetween('payment_date', [$startDate, $endDate])
            ->sum('amount');

        // 4. حساب الرصيد المتبقي لجميع الفواتير (ليس فقط في هذه الفترة)
        $totalOutstanding = DB::table('invoices')
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->sum(DB::raw('total_amount - paid_amount'));

        // 5. جلب آخر 10 مدفوعات تمت كعينة من النشاط الأخير
        $recentPayments = Payment::with('invoice.client', 'receivedBy')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->latest('payment_date')
            ->limit(10)
            ->get();

        // 6. تجميع البيانات في استجابة JSON منظمة
        return response()->json([
            'summary' => [
                'total_invoiced' => (float) $totalInvoiced,
                'total_paid' => (float) $totalPaid,
                'total_outstanding' => (float) $totalOutstanding,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'recent_payments' => \App\Http\Resources\PaymentResource::collection($recentPayments),
        ]);
    }
}
