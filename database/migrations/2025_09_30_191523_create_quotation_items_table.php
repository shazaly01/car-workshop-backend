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
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id(); // المعرّف الرقمي الفريد للبند

            // --- الربط مع جدول عرض السعر الرئيسي ---
            $table->foreignId('quotation_id')
                  ->constrained('quotations')
                  ->onDelete('cascade'); // إذا حُذف عرض السعر، تُحذف كل بنوده تلقائياً

            // --- تفاصيل البند ---
            $table->string('description'); // وصف البند (مثال: "أجور تغيير زيت المحرك" أو "فلتر زيت أصلي")
            $table->enum('type', ['service', 'part'])->default('service'); // نوع البند: خدمة أم قطعة غيار
            $table->decimal('quantity', 8, 2); // الكمية (قد تكون 1.5 ساعة عمل، لذا decimal أفضل)
            $table->decimal('unit_price', 10, 2); // سعر الوحدة (سعر القطعة الواحدة أو سعر ساعة العمل)

            // --- الحقل المالي المحسوب ---
            $table->decimal('total_price', 10, 2); // المجموع (الكمية * سعر الوحدة)

            // --- الربط مع جدول المخزون (خطوة مستقبلية اختيارية) ---
            // $table->foreignId('part_id')->nullable()->constrained('parts');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};
