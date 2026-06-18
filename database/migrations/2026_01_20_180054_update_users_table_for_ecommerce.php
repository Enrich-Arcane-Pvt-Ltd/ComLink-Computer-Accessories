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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');

            $table->string('first_name')->after('id');
            $table->string('last_name')->after('first_name');

            $table->string('customer_id')->unique()->nullable()->after('id');
            $table->string('phone')->nullable()->index();
            $table->string('secondary_phone')->nullable()->index();

            $table->string('avatar_url')->nullable();

            $table->string('role')->default('user')->index();
            $table->string('status')->default('active')->index();
            $table->boolean('is_guest')->default(false)->index();

            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
