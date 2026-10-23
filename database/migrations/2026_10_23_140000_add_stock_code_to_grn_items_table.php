<?php

use App\Models\GrnItem;
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
        if (! Schema::hasColumn('grn_items', 'stock_code')) {
            Schema::table('grn_items', function (Blueprint $table) {
                $table->string('stock_code')->nullable()->after('product_code')->index();
            });
        }

        // Backfill existing grn_items with deterministic stock codes
        try {
            $items = GrnItem::orderBy('id', 'asc')->get();
            $batchCounters = [];
            foreach ($items as $item) {
                if (empty($item->stock_code)) {
                    $prefix = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $item->product_code));
                    if (empty($prefix)) {
                        $prefix = 'ITEM' . $item->id;
                    }
                    $colorPart = ! empty($item->color_name)
                        ? '-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string) $item->color_name), 0, 3))
                        : '';
                    $key = $item->product_code . '_' . ($item->color_name ?? '');
                    $batchCounters[$key] = ($batchCounters[$key] ?? 0) + 1;
                    $batchNum = str_pad((string) $batchCounters[$key], 3, '0', STR_PAD_LEFT);

                    $item->stock_code = "STK-{$prefix}{$colorPart}-{$batchNum}";
                    $item->saveQuietly();
                }
            }
        } catch (\Throwable $e) {
            // Ignore in migration if table empty or during fresh setups
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('grn_items', 'stock_code')) {
            Schema::table('grn_items', function (Blueprint $table) {
                $table->dropColumn('stock_code');
            });
        }
    }
};
