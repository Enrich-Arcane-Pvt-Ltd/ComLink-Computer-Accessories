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
            if (! Schema::hasColumn('products', 'warranty_duration')) {
                $table->integer('warranty_duration')->nullable()->after('condition');
            }
            if (! Schema::hasColumn('products', 'warranty_unit')) {
                $table->string('warranty_unit', 20)->nullable()->default('Years')->after('warranty_duration');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['warranty_duration', 'warranty_unit']);
        });
    }
};
