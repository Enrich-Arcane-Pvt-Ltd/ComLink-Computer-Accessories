<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create suppliers table
        if (! Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('contact_no')->nullable();
                $table->string('email')->nullable();
                $table->text('address')->nullable();
                $table->timestamps();
            });
        }

        // 2. Add supplier_id, stock_added_count, and stock_added_date to products table
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'supplier_id')) {
                $table->foreignId('supplier_id')
                    ->nullable()
                    ->after('brand_id')
                    ->constrained('suppliers')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('products', 'stock_added_count')) {
                $table->integer('stock_added_count')
                    ->nullable()
                    ->after('quantity');
            }

            if (! Schema::hasColumn('products', 'stock_added_date')) {
                $table->dateTime('stock_added_date')
                    ->nullable()
                    ->after('stock_added_count');
            }
        });

        // 3. Seed default sample suppliers if table is empty
        if (DB::table('suppliers')->count() === 0) {
            $now = now();
            DB::table('suppliers')->insert([
                [
                    'name' => 'TechZone Distributors (Pvt) Ltd',
                    'contact_no' => '+94 11 234 5678',
                    'email' => 'techzone.dist@comlink.lk',
                    'address' => 'No. 45, Galle Road, Colombo 03, Sri Lanka',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'Redington Hardware Supplies',
                    'contact_no' => '+94 11 789 0123',
                    'email' => 'sales@redington.lk',
                    'address' => 'Level 8, Unity Plaza, Colombo 04, Sri Lanka',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'EWIS Peripherals & Components',
                    'contact_no' => '+94 11 456 7890',
                    'email' => 'orders@ewis.lk',
                    'address' => 'No. 120, High Level Road, Nugegoda, Sri Lanka',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }

        // 4. Backfill existing products so stock_added_count and stock_added_date have valid values
        $firstSupplierId = DB::table('suppliers')->value('id');
        DB::table('products')->whereNull('stock_added_count')->update([
            'stock_added_count' => DB::raw('COALESCE(quantity, 10)'),
            'stock_added_date' => DB::raw('COALESCE(created_at, NOW())'),
            'supplier_id' => $firstSupplierId,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'supplier_id')) {
                $table->dropForeign(['supplier_id']);
                $table->dropColumn('supplier_id');
            }
            if (Schema::hasColumn('products', 'stock_added_count')) {
                $table->dropColumn('stock_added_count');
            }
            if (Schema::hasColumn('products', 'stock_added_date')) {
                $table->dropColumn('stock_added_date');
            }
        });

        Schema::dropIfExists('suppliers');
    }
};
