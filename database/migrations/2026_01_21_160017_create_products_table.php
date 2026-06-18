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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Basic Info
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->index();
            $table->string('slug')->unique();
            $table->string('sku')->unique()->nullable();
            $table->longText('description')->nullable();

            $table->json('images')->nullable();

            // Pricing
            $table->decimal('price', 10, 2)->index();
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->decimal('cost_price', 10, 2)->nullable();

            // Inventory
            $table->integer('quantity')->default(0);
            $table->integer('security_stock')->default(10);

            // Status & Settings
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->enum('type', ['simple', 'variable'])->default('simple');
            $table->date('published_at')->nullable();

            $table->timestamps();

            $table->index(['is_visible', 'is_featured', 'price']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
