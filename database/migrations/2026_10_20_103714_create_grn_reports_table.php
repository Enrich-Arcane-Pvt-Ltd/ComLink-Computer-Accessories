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
        Schema::create('grn_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_number')->unique()->index();
            $table->string('title');
            $table->string('analysis_type')->default('supplier_wise'); // supplier_wise or product_wise
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('product_code')->nullable()->index();
            $table->date('date_from');
            $table->date('date_to');
            $table->unsignedInteger('total_grns')->default(0);
            $table->unsignedInteger('total_items_count')->default(0);
            $table->unsignedInteger('total_quantity')->default(0);
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->json('report_data')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grn_reports');
    }
};
