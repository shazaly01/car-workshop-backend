<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   // في database/migrations/[timestamp]_add_catalog_item_id_to_invoice_items_table.php
public function up(): void
{
    Schema::table('invoice_items', function (Blueprint $table) {
        $table->foreignId('catalog_item_id')->nullable()->after('invoice_id')->constrained('catalog_items')->onDelete('set null');
    });
}

public function down(): void
{
    Schema::table('invoice_items', function (Blueprint $table) {
        $table->dropForeign(['catalog_item_id']);
        $table->dropColumn('catalog_item_id');
    });
}

};
