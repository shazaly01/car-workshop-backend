<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Invoice;
use App\Models\Payment; // <-- 1. استيراد النموذج الصحيح
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Exception;

class PaymentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Store a new payment for an invoice.
     *
     * @param \App\Http\Requests\StorePaymentRequest $request
     * @param \App\Models\Invoice $invoice
     * @return \Illuminate\Http\JsonResponse|\App\Http\Resources\PaymentResource
     */
    public function store(StorePaymentRequest $request, Invoice $invoice): JsonResponse | PaymentResource
    {
        // 2. التحقق من الصلاحية الصحيحة: هل يمكن للمستخدم إنشاء دفعة لهذه الفاتورة؟
        $this->authorize('create', [Payment::class, $invoice]);

        $validatedData = $request->validated();
        $paymentAmount = (float) $validatedData['amount'];

        try {
            // استخدام Transaction لضمان سلامة البيانات
            $payment = DB::transaction(function () use ($invoice, $validatedData, $paymentAmount) {

                // قفل صف الفاتورة لمنع أي عمليات أخرى من تعديله أثناء الدفع
                $invoice = Invoice::where('id', $invoice->id)->lockForUpdate()->first();

                // إعادة التحقق من منطق العمل داخل الـ Transaction بعد القفل
                $remainingAmount = round($invoice->total_amount - $invoice->paid_amount, 2);
                if (round($paymentAmount, 2) > $remainingAmount) {
                    // إطلاق استثناء لإيقاف الـ Transaction وإرجاع رسالة خطأ
                    throw new Exception('مبلغ الدفعة أكبر من المبلغ المتبقي على الفاتورة. المتبقي: ' . $remainingAmount);
                }

                // إنشاء سجل الدفعة
                $payment = $invoice->payments()->create([
                    'amount' => $paymentAmount,
                    'payment_date' => $validatedData['payment_date'],
                    'payment_method' => $validatedData['payment_method'],
                    'transaction_reference' => $validatedData['transaction_reference'] ?? null,
                    'notes' => $validatedData['notes'] ?? null,
                    'received_by_user_id' => Auth::id(),
                ]);

                // تحديث المبلغ المدفوع في الفاتورة
                $invoice->paid_amount += $paymentAmount;

                // تحديث حالة الفاتورة إذا تم سدادها بالكامل
                if (round($invoice->paid_amount, 2) >= round($invoice->total_amount, 2)) {
                    $invoice->status = 'paid';
                    // ضمان عدم تجاوز المبلغ الإجمالي بسبب أخطاء التقريب
                    $invoice->paid_amount = $invoice->total_amount;
                } else {
                    $invoice->status = 'partially_paid';
                }

                $invoice->save();

                return $payment;
            });

            // تحميل علاقة الموظف الذي استلم الدفعة
            $payment->load('receivedBy');

            return new PaymentResource($payment);

        } catch (Exception $e) {
            // التقاط الاستثناء الذي تم إطلاقه من داخل الـ Transaction
            return response()->json([
                'message' => $e->getMessage(),
            ], 422); // Unprocessable Entity
        }
    }
}
