<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix case-sensitive Linux image path for Peripherals
        DB::table('categories')
            ->where('slug', 'peripherals')
            ->orWhere('image', 'images/categories/Peripherals.png')
            ->update([
                'image' => 'images/categories/peripherals.png',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
