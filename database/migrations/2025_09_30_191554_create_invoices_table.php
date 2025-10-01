<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id(); // المعرّف الرقمي الفريد للفاتورة

            // --- الربط مع أمر العمل والجهة ---
            $table->foreignId('work_order_id')->constrained('work_orders');
            $table->foreignId('client_id')->constrained('clients'); // نكرر الربط مع العميل لتسهيل الاستعلامات المحاسبية

            // --- بيانات الفاتورة ---
            $table->string('number')->unique(); // رقم فريد للفاتورة (مثل: INV-2024-0001)
            $table->date('issue_date'); // تاريخ إصدار الفاتورة
            $table->date('due_date'); // تاريخ استحقاق الدفع

            // --- الحالة المالية ---
            $table->string('status')->default('unpaid'); // الحالة: unpaid, paid, partially_paid, overdue, cancelled

            // --- الحقول المالية ---
            $table->decimal('subtotal', 10, 2); // المجموع الفرعي قبل الضريبة
            $table->decimal('tax_percentage', 5, 2)->default(0); // نسبة الضريبة (مثال: 15.00)
            $table->decimal('tax_amount', 10, 2); // قيمة الضريبة المحسوبة
            $table->decimal('total_amount', 10, 2); // المجموع النهائي المطلوب
            $table->decimal('paid_amount', 10, 2)->default(0); // المبلغ المدفوع حتى الآن

            // --- ملاحظات ---
            $table->text('notes')->nullable(); // أي ملاحظات إضافية على الفاتورة

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
