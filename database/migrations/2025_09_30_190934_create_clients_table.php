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
        Schema::create('clients', function (Blueprint $table) {
            $table->id(); // المعرّف الرقمي الفريد للجهة (Primary Key)

            // --- بيانات الجهة الأساسية ---
            $table->string('name'); // اسم الجهة أو العميل
            $table->string('phone')->unique(); // رقم الهاتف (يجب أن يكون فريداً لمنع التكرار)
            $table->string('email')->unique()->nullable(); // البريد الإلكتروني (فريد واختياري)
            $table->string('address')->nullable(); // العنوان (اختياري)
            $table->enum('client_type', ['individual', 'company'])->default('company'); // نوع الجهة (فرد أم شركة)

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
        Schema::dropIfExists('clients');
    }
};
