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
        Schema::create('payments', function (Blueprint $table) {
            $table->id(); // المعرّف الرقمي الفريد لعملية الدفع

            // --- الربط مع الفاتورة والمستخدم ---
            $table->foreignId('invoice_id')->constrained('invoices'); // ربط الدفعة بالفاتورة التي تسددها
            $table->foreignId('received_by_user_id')->nullable()->constrained('users'); // الموظف الذي استلم المبلغ

            // --- تفاصيل الدفع ---
            $table->decimal('amount', 10, 2); // المبلغ المدفوع في هذه العملية
            $table->date('payment_date'); // تاريخ الدفع
            $table->string('payment_method'); // طريقة الدفع (مثال: 'cash', 'card', 'bank_transfer')
            $table->string('transaction_reference')->nullable(); // رقم مرجعي لعملية الدفع (للتحويلات أو البطاقات)

            // --- ملاحظات ---
            $table->text('notes')->nullable(); // أي ملاحظات على عملية الدفع

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
