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
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id(); // المعرّف الرقمي الفريد لأمر العمل

            // --- الربط مع الجداول الأخرى (الآن بشكل فعلي) ---
            $table->foreignId('client_id')->constrained('clients'); // ربط أمر العمل بالجهة
            $table->foreignId('vehicle_id')->constrained('vehicles'); // ربط أمر العمل بالسيارة
            // ملاحظة: لا نستخدم onDelete('cascade') هنا غالباً،
            // لأنه من الأفضل عدم حذف أمر عمل تلقائياً عند حذف سيارة.
            // يمكن التحكم بهذا منطقياً داخل التطبيق.

            // --- تفاصيل أمر العمل ---
            $table->string('number')->unique(); // رقم مرجعي فريد لأمر العمل (مثل WO-2024-0001)
            $table->text('client_complaint'); // شكوى العميل (لا يجب أن تكون اختيارية)
            $table->text('initial_inspection_notes')->nullable(); // ملاحظات الفحص الأولي (اختياري)

            // --- حالة أمر العمل (Workflow Status) ---
            $table->string('status')->default('pending_diagnosis');
            // الحالات الممكنة:
            // 'pending_diagnosis'      -> بانتظار التشخيص
            // 'diagnosing'             -> جاري التشخيص
            // 'pending_quote_approval' -> بانتظار موافقة عرض السعر
            // 'quote_approved'         -> تمت الموافقة على عرض السعر
            // 'quote_rejected'         -> تم رفض عرض السعر
            // 'in_progress'            -> قيد الإصلاح
            // 'pending_parts'          -> بانتظار قطع غيار
            // 'quality_check'          -> في مرحلة فحص الجودة
            // 'ready_for_delivery'     -> جاهز للتسليم
            // 'completed'              -> مكتمل ومغلق
            // 'cancelled'              -> ملغي

            // --- الربط مع المستخدم الذي أنشأ الأمر (اختياري لكن مفيد) ---
            $table->foreignId('created_by_user_id')->nullable()->constrained('users');

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
        Schema::dropIfExists('work_orders');
    }
};
