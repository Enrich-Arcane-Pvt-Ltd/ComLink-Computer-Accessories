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
        // 1. Add refund tracking fields to orders table
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'refund_status')) {
                $table->string('refund_status')->default('none')->after('payment_status'); // none, partial, full
            }
            if (! Schema::hasColumn('orders', 'refunded_amount')) {
                $table->decimal('refunded_amount', 12, 2)->default(0)->after('total_amount');
            }
        });

        // 2. Customer Returns (Refunds) header table
        Schema::create('customer_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('refund_amount', 12, 2)->default(0);
            $table->string('refund_method')->default('cash'); // cash, bank_transfer, card_refund, store_credit
            $table->string('status')->default('approved'); // approved, pending, rejected
            $table->string('reason')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 3. Customer Return Items table (tracks damaged/good labeling & resolution state)
        Schema::create('customer_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_return_id')->constrained('customer_returns')->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('product_name');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('refund_subtotal', 12, 2)->default(0);
            $table->decimal('unit_cost', 12, 2)->default(0); // COGS / GRN unit cost at time of return
            $table->string('condition')->default('damaged'); // damaged, good
            $table->string('resolution_status')->default('in_damaged_stock'); // in_damaged_stock, restocked, sent_to_supplier, supplier_credited, supplier_replaced, scrapped
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 4. Supplier Returns (RMA / Debit Note) header table
        Schema::create('supplier_returns', function (Blueprint $table) {
            $table->id();
            $table->string('rma_number')->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('grn_id')->nullable()->constrained('grns')->nullOnDelete();
            $table->string('status')->default('pending'); // pending, dispatched, credited, replaced, rejected
            $table->string('resolution_type')->nullable(); // credit_refund, replacement, rejected
            $table->decimal('total_cost_value', 12, 2)->default(0);
            $table->decimal('credit_amount', 12, 2)->default(0);
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 5. Supplier Return Items table
        Schema::create('supplier_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_return_id')->constrained('supplier_returns')->cascadeOnDelete();
            $table->foreignId('customer_return_item_id')->nullable()->constrained('customer_return_items')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->foreignId('grn_item_id')->nullable()->constrained('grn_items')->nullOnDelete();
            $table->string('product_name');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_return_items');
        Schema::dropIfExists('supplier_returns');
        Schema::dropIfExists('customer_return_items');
        Schema::dropIfExists('customer_returns');

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'refund_status')) {
                $table->dropColumn('refund_status');
            }
            if (Schema::hasColumn('orders', 'refunded_amount')) {
                $table->dropColumn('refunded_amount');
            }
        });
    }
};
