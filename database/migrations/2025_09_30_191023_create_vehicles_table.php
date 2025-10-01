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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id(); // المعرّف الرقمي الفريد للسيارة (Primary Key)

            // --- الربط مع جدول الجهات (clients) ---
            $table->foreignId('client_id') // هذا هو المفتاح الأجنبي
                  ->constrained('clients') // يحدد أن هذا المفتاح مرتبط بجدول clients
                  ->onDelete('cascade'); // <-- نقطة مهمة جداً

            // --- بيانات تعريف السيارة ---
            $table->string('vin')->unique(); // رقم الهيكل (VIN). يجب أن يكون فريداً عالمياً.
            $table->string('plate_number')->unique(); // رقم اللوحة. يجب أن يكون فريداً أيضاً.
            $table->string('make'); // الشركة المصنعة (مثال: Toyota)
            $table->string('model'); // الطراز (مثال: Camry)
            $table->year('year'); // سنة الصنع (مثال: 2023)
            $table->string('color')->nullable(); // اللون (اختياري)

            // --- بيانات إضافية ---
            $table->unsignedInteger('mileage')->nullable(); // قراءة عداد الكيلومترات الحالية

            // --- الحقول التلقائية ---
            $table->timestamps(); // يضيف created_at و updated_at
            $table->softDeletes(); // يضيف deleted_at لتفعيل الحذف الناعم
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
