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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Order Status
            $table->enum('status', ['Processing', 'Packaging', 'Shipping', 'Delivered'])->default('Processing');

            // User Information
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->string('customer_secondary_phone')->nullable();

            // Delivery & Shipping Address
            $table->string('shipping_address_line_1');
            $table->string('shipping_address_line_2')->nullable();
            $table->string('shipping_city');
            $table->string('shipping_state')->nullable();
            $table->string('shipping_postal_code')->nullable();
            $table->string('shipping_country')->default('Sri Lanka');

            // Pricing & Totals
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('shipping_fee', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2)->default(0.00);

            // Payment Details
            $table->string('payment_method')->default('Stripe Card');
            $table->string('payment_status')->default('Paid');
            $table->string('stripe_payment_intent_id')->nullable();

            // Courier / Shipping metadata
            $table->string('courier_name')->nullable();
            $table->string('tracking_number')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();

            $table->string('product_name');
            $table->string('product_image')->nullable();
            $table->string('category_name')->nullable();
            $table->decimal('unit_price', 12, 2)->default(0.00);
            $table->integer('quantity')->default(1);
            $table->decimal('total_price', 12, 2)->default(0.00);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
