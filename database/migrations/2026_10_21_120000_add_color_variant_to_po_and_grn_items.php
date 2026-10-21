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
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable()->after('inventory_product_id')->constrained('product_variants')->nullOnDelete();
            $table->string('color_name')->nullable()->after('product_name');
        });

        Schema::table('grn_items', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable()->after('inventory_product_id')->constrained('product_variants')->nullOnDelete();
            $table->string('color_name')->nullable()->after('product_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grn_items', function (Blueprint $table) {
            $table->dropForeign(['product_variant_id']);
            $table->dropColumn(['product_variant_id', 'color_name']);
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropForeign(['product_variant_id']);
            $table->dropColumn(['product_variant_id', 'color_name']);
        });
    }
};
