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
        // 1. Inventory Products (Supplier-provided catalog)
        Schema::create('inventory_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->string('product_code')->unique();
            $table->string('product_name');
            $table->string('product_image')->nullable();
            $table->timestamps();
        });

        // 2. Purchase Orders
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->string('status')->default('Planning'); // Planning, Approved, Rejected
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Purchase Order Items
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('inventory_product_id')->nullable()->constrained('inventory_products')->nullOnDelete();
            $table->string('product_code');
            $table->string('product_name');
            $table->integer('product_quantity')->default(1);
            $table->string('product_condition')->default('Brandnew'); // Brandnew, Used
            $table->timestamps();
        });

        // 4. Goods Received Notes (GRN)
        Schema::create('grns', function (Blueprint $table) {
            $table->id();
            $table->string('grn_number')->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->decimal('sub_total', 14, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. GRN Items (Received inventory batches)
        Schema::create('grn_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grn_id')->constrained('grns')->cascadeOnDelete();
            $table->foreignId('inventory_product_id')->nullable()->constrained('inventory_products')->nullOnDelete();
            $table->string('product_code');
            $table->string('product_name');
            $table->string('product_condition')->default('Brandnew'); // Brandnew, Used
            $table->decimal('price_per_unit', 14, 2)->default(0.00);
            $table->integer('product_quantity')->default(1);
            $table->decimal('total', 14, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grn_items');
        Schema::dropIfExists('grns');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('inventory_products');
    }
};
