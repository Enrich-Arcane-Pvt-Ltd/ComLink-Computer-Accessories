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
        Schema::table('grns', function (Blueprint $table) {
            if (! Schema::hasColumn('grns', 'grn_type')) {
                $table->string('grn_type')->default('po')->after('grn_number');
            }
            if (! Schema::hasColumn('grns', 'direct_po_number')) {
                $table->string('direct_po_number')->nullable()->after('purchase_order_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grns', function (Blueprint $table) {
            $table->dropColumn(['grn_type', 'direct_po_number']);
        });
    }
};

