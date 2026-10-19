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
        Schema::create('product_specifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('sub_category_id')->nullable()->constrained('categories')->cascadeOnDelete();
            $table->string('specification_type'); // 'main' or 'technical'
            $table->string('sub_section')->nullable(); // 'general', 'performance', 'compatibility'
            $table->string('title');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['category_id', 'sub_category_id']);
            $table->index('specification_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_specifications');
    }
};
