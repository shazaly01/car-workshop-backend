<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_items', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique()->nullable()->comment('Stock Keeping Unit - كود الصنف الفريد');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['part', 'service'])->comment('نوع الصنف: قطعة غيار أو خدمة');
            $table->decimal('unit_price', 10, 2)->default(0.00)->comment('سعر البيع الافتراضي');
            $table->boolean('is_active')->default(true)->comment('هل الصنف نشط ويمكن استخدامه؟');
            $table->timestamps();
            $table->softDeletes(); // للسماح بالحذف الناعم للأصناف
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_items');
    }
};
