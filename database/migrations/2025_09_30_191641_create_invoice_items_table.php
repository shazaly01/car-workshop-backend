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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id(); // المعرّف الرقمي الفريد للبند

            // --- الربط مع جدول الفاتورة الرئيسي ---
            $table->foreignId('invoice_id')
                  ->constrained('invoices')
                  ->onDelete('cascade'); // إذا حُذفت الفاتورة، تُحذف كل بنودها تلقائياً

            // --- تفاصيل البند ---
            $table->string('description'); // وصف البند (مثال: "أجور تغيير زيت المحرك" أو "فلتر زيت أصلي")
            $table->enum('type', ['service', 'part'])->default('service'); // نوع البند: خدمة أم قطعة غيار
            $table->decimal('quantity', 8, 2); // الكمية
            $table->decimal('unit_price', 10, 2); // سعر الوحدة

            // --- الحقل المالي المحسوب ---
            $table->decimal('total_price', 10, 2); // المجموع (الكمية * سعر الوحدة)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
