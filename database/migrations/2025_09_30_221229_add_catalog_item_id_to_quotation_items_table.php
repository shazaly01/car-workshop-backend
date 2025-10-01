<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   // في database/migrations/[timestamp]_add_catalog_item_id_to_quotation_items_table.php
public function up(): void
{
    Schema::table('quotation_items', function (Blueprint $table) {
        $table->foreignId('catalog_item_id')->nullable()->after('quotation_id')->constrained('catalog_items')->onDelete('set null');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            //
        });
    }
};
