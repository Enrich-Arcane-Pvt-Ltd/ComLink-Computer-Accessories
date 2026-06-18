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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('shop_name')->default('Vicky-Store');
            $table->string('shop_email')->nullable();
            $table->string('shop_phone')->nullable();
            $table->text('shop_address')->nullable();
            $table->string('currency_symbol')->default('LKR');
            $table->string('location_lat')->nullable(); // Google Maps සඳහා
            $table->string('location_long')->nullable();
            $table->json('social_links')->nullable(); // Facebook, Insta links
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
