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
        Schema::create('diagnoses', function (Blueprint $table) {
            $table->id(); // المعرّف الرقمي الفريد للتشخيص

            // --- الربط مع أمر العمل والمستخدم ---
            $table->foreignId('work_order_id')->unique()->constrained('work_orders'); // كل أمر عمل له تشخيص واحد فقط، لذا نستخدم unique
            $table->foreignId('technician_id')->nullable()->constrained('users'); // ربط التشخيص بالفني (المستخدم) الذي قام به

            // --- نتائج الفحص ---
            $table->json('obd_codes')->nullable(); // لتخزين أكواد OBD-II بصيغة JSON (مرنة جداً)
            $table->text('manual_inspection_results'); // نتائج الفحص اليدوي (ملاحظات الفني)
            $table->text('proposed_repairs')->nullable(); // الإصلاحات المقترحة بناءً على التشخيص

            // --- الحقول التلقائية ---
            $table->timestamps(); // يضيف created_at و updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diagnoses');
    }
};
