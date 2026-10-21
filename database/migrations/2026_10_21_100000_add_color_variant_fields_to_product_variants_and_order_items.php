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
        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('color_name')->nullable()->after('product_id');
            $table->string('variant_name')->nullable()->after('color_name');
            $table->string('condition')->default('Brand New')->after('variant_name');
            $table->integer('inventory_stock')->default(0)->after('stock');
            $table->integer('show_quantity')->default(0)->after('inventory_stock');
            $table->decimal('grn_price', 12, 2)->nullable()->after('show_quantity');
            $table->decimal('sale_price', 12, 2)->nullable()->after('price');
            $table->integer('discount_percentage')->default(0)->after('sale_price');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('variant_id')->nullable()->after('product_id')->constrained('product_variants')->nullOnDelete();
            $table->string('color')->nullable()->after('product_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['variant_id']);
            $table->dropColumn(['variant_id', 'color']);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn([
                'color_name',
                'variant_name',
                'condition',
                'inventory_stock',
                'show_quantity',
                'grn_price',
                'sale_price',
                'discount_percentage',
            ]);
        });
    }
};
