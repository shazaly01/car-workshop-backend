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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id(); // المعرّف الرقمي الفريد لعرض السعر

            // --- الربط مع أمر العمل ---
            $table->foreignId('work_order_id')->constrained('work_orders');
            // يمكن لأمر العمل الواحد أن يكون له عدة عروض أسعار (مثلاً: عرض سعر أولي ثم عرض معدل)
            // لذا لا نستخدم unique هنا

            // --- بيانات عرض السعر ---
            $table->string('number')->unique(); // رقم فريد لعرض السعر (مثل: Q-2024-0001)
            $table->date('issue_date'); // تاريخ إصدار عرض السعر
            $table->date('expiry_date'); // تاريخ انتهاء صلاحية العرض
            $table->string('status')->default('pending'); // الحالة: pending, approved, rejected, expired

            // --- الحقول المالية (سيتم حسابها من البنود) ---
            $table->decimal('subtotal', 10, 2)->default(0); // المجموع الفرعي قبل الضريبة
            $table->decimal('tax_amount', 10, 2)->default(0); // قيمة الضريبة
            $table->decimal('total_amount', 10, 2)->default(0); // المجموع النهائي

            // --- ملاحظات ---
            $table->text('notes')->nullable(); // أي ملاحظات إضافية على عرض السعر

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
