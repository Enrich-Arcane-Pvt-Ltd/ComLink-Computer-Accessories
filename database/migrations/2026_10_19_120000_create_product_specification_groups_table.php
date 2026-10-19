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
        Schema::create('product_specification_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('sub_category_id')->nullable()->constrained('categories')->cascadeOnDelete();
            $table->json('main_specs')->nullable();
            $table->json('general_specs')->nullable();
            $table->json('performance_specs')->nullable();
            $table->json('compatibility_specs')->nullable();
            $table->timestamps();

            $table->index(['category_id', 'sub_category_id']);
        });

        // Migrate existing product_specifications data into groups
        if (Schema::hasTable('product_specifications')) {
            $specs = DB::table('product_specifications')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            $grouped = [];
            foreach ($specs as $spec) {
                $groupKey = $spec->category_id . '_' . ($spec->sub_category_id ?? 'null');
                if (! isset($grouped[$groupKey])) {
                    $grouped[$groupKey] = [
                        'category_id' => $spec->category_id,
                        'sub_category_id' => $spec->sub_category_id,
                        'main_specs' => [],
                        'general_specs' => [],
                        'performance_specs' => [],
                        'compatibility_specs' => [],
                    ];
                }

                if ($spec->specification_type === 'main') {
                    if (! in_array($spec->title, $grouped[$groupKey]['main_specs'])) {
                        $grouped[$groupKey]['main_specs'][] = $spec->title;
                    }
                } elseif ($spec->specification_type === 'technical') {
                    if ($spec->sub_section === 'general') {
                        if (! in_array($spec->title, $grouped[$groupKey]['general_specs'])) {
                            $grouped[$groupKey]['general_specs'][] = $spec->title;
                        }
                    } elseif ($spec->sub_section === 'performance') {
                        if (! in_array($spec->title, $grouped[$groupKey]['performance_specs'])) {
                            $grouped[$groupKey]['performance_specs'][] = $spec->title;
                        }
                    } elseif ($spec->sub_section === 'compatibility') {
                        if (! in_array($spec->title, $grouped[$groupKey]['compatibility_specs'])) {
                            $grouped[$groupKey]['compatibility_specs'][] = $spec->title;
                        }
                    }
                }
            }

            $now = now();
            foreach ($grouped as $data) {
                DB::table('product_specification_groups')->insert([
                    'category_id' => $data['category_id'],
                    'sub_category_id' => $data['sub_category_id'],
                    'main_specs' => json_encode(array_values($data['main_specs'])),
                    'general_specs' => json_encode(array_values($data['general_specs'])),
                    'performance_specs' => json_encode(array_values($data['performance_specs'])),
                    'compatibility_specs' => json_encode(array_values($data['compatibility_specs'])),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_specification_groups');
    }
};
