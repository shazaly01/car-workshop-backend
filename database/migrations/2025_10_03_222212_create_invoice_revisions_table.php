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
        Schema::create('invoice_revisions', function (Blueprint $table) {
            $table->id();

            // 1. رابط الفاتورة التي تم تعديلها
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');

            // 2. رابط المستخدم الذي قام بالتعديل
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');

            // 3. سبب التعديل (إلزامي)
            $table->string('reason')->nullable(); // <-- أضف nullable()


            // 4. المبالغ قبل وبعد التعديل للتوثيق
            $table->decimal('old_amount', 10, 2);
            $table->decimal('new_amount', 10, 2);

            // 5. تاريخ ووقت التعديل
            $table->timestamps(); // سيضيف created_at و updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_revisions');
    }
};
