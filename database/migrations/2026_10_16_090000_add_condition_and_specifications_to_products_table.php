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
            if (! Schema::hasColumn('products', 'condition')) {
                $table->string('condition')->default('Brand New')->after('type');
            }
            if (! Schema::hasColumn('products', 'main_specifications')) {
                $table->json('main_specifications')->nullable()->after('description');
            }
            if (! Schema::hasColumn('products', 'technical_specifications')) {
                $table->json('technical_specifications')->nullable()->after('main_specifications');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['condition', 'main_specifications', 'technical_specifications']);
        });
    }
};
