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
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'product_code')) {
                $table->string('product_code')->nullable()->after('supplier_id')->index();
            }

            if (! Schema::hasColumn('products', 'inventory_stock')) {
                $table->integer('inventory_stock')->default(0)->after('quantity');
            }

            if (! Schema::hasColumn('products', 'show_quantity')) {
                $table->integer('show_quantity')->default(0)->after('inventory_stock');
            }

            if (! Schema::hasColumn('products', 'grn_price')) {
                $table->decimal('grn_price', 10, 2)->nullable()->default(0.00)->after('cost_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['product_code', 'inventory_stock', 'show_quantity', 'grn_price']);
        });
    }
};
