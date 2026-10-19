<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations to seed initial product specifications.
     */
    public function up(): void
    {
        $categories = DB::table('categories')->get()->keyBy('slug');

        $mainSpecsDefinitions = [
            // Laptops
            'laptops' => [
                'sub' => null,
                'specs' => ['Display size', 'Graphic cards', 'Memory', 'Processor', 'Storage', 'Type'],
            ],
            // PC Components
            'pc-components-processors' => [
                'cat' => 'pc-components',
                'sub' => 'processors',
                'specs' => ['Generation', 'CORES', 'THREADS'],
            ],
            'pc-components-motherboards' => [
                'cat' => 'pc-components',
                'sub' => 'motherboards',
                'specs' => ['Performance Level', 'CPU Soket', 'Chipset Model', 'MB Platform'],
            ],
            'pc-components-coolers' => [
                'cat' => 'pc-components',
                'sub' => 'coolers',
                'specs' => ['Fans Size', 'Radiator Size', 'Cooling Type'],
            ],
            'pc-components-memory' => [
                'cat' => 'pc-components',
                'sub' => 'memory',
                'specs' => ['RAM Capacity', 'Memory Speed', 'Form Factor', 'Memory Gen'],
            ],
            'pc-components-ssd' => [
                'cat' => 'pc-components',
                'sub' => 'ssd',
                'specs' => ['Generation', 'Storage Capacity', 'SSD Type'],
            ],
            'pc-components-hard-disk' => [
                'cat' => 'pc-components',
                'sub' => 'hard-disk',
                'specs' => ['HDD Color Type', 'Storage Capacity', 'Storage Type'],
            ],
            'pc-components-graphic-cards' => [
                'cat' => 'pc-components',
                'sub' => 'graphic-cards',
                'specs' => ['GPU Series', 'Memory(VRAM)', 'Accessory Type', 'Power Supply Requirement'],
            ],
            'pc-components-power-supply' => [
                'cat' => 'pc-components',
                'sub' => 'power-supply',
                'specs' => ['Power Output', 'Modular', 'Power Rating'],
            ],
            'pc-components-pc-cases' => [
                'cat' => 'pc-components',
                'sub' => 'pc-cases',
                'specs' => ['Accessory Type', 'Cooler Size', 'MB Size'],
            ],
            // Monitors & Displays
            'monitors-displays' => [
                'sub' => null,
                'specs' => ['Panel', 'Screen Size'],
            ],
            // Gaming - Laptops
            'gaming-laptops' => [
                'cat' => 'gaming',
                'sub' => 'gaming-laptops',
                'specs' => ['Display size', 'Graphic cards', 'Memory', 'Processor', 'Storage', 'Type'],
            ],
            // Products - Computers
            'products-computers' => [
                'cat' => 'products',
                'sub' => 'computers',
                'specs' => ['Computer Type', 'Processor', 'Graphics Card', 'Memory (RAM)', 'Storage', 'Warranty'],
            ],
            // Products - Toners and Cartridges
            'products-toners-cartridges' => [
                'cat' => 'products',
                'sub' => 'toners-cartridges',
                'specs' => ['Type'],
            ],
        ];

        $now = now();

        // 1. Insert Main Specifications
        foreach ($mainSpecsDefinitions as $def) {
            $catSlug = $def['cat'] ?? null;
            if (! $catSlug && isset($categories['laptops']) && $def === $mainSpecsDefinitions['laptops']) {
                $catSlug = 'laptops';
            } elseif (! $catSlug && isset($categories['monitors-displays'])) {
                $catSlug = 'monitors-displays';
            }

            $cat = $catSlug && isset($categories[$catSlug]) ? $categories[$catSlug] : null;
            if (! $cat) {
                continue;
            }

            $sub = ! empty($def['sub']) && isset($categories[$def['sub']]) ? $categories[$def['sub']] : null;

            $order = 1;
            foreach ($def['specs'] as $specTitle) {
                DB::table('product_specifications')->updateOrInsert(
                    [
                        'category_id' => $cat->id,
                        'sub_category_id' => $sub?->id,
                        'specification_type' => 'main',
                        'title' => $specTitle,
                    ],
                    [
                        'sub_section' => null,
                        'sort_order' => $order++,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }

        // 2. Insert Technical Specifications for each main category
        $mainCategories = DB::table('categories')->whereNull('parent_id')->get();

        $techSpecs = [
            'general' => [
                'Model / Series',
                'Category Segment',
                'Condition',
            ],
            'performance' => [
                'Interface / Connection',
                'Architecture',
                'Thermal Efficiency',
                'Power Consumption',
            ],
            'compatibility' => [
                'Operating Systems',
                'Certification',
                'Dimensions & Weight',
                'Included in Box',
            ],
        ];

        foreach ($mainCategories as $cat) {
            // Find all subcategories or if none, category itself
            $subs = DB::table('categories')->where('parent_id', $cat->id)->get();
            $targetSubs = $subs->isNotEmpty() ? $subs : [null];

            foreach ($targetSubs as $sub) {
                foreach ($techSpecs as $subSection => $specTitles) {
                    $order = 1;
                    foreach ($specTitles as $specTitle) {
                        DB::table('product_specifications')->updateOrInsert(
                            [
                                'category_id' => $cat->id,
                                'sub_category_id' => $sub?->id,
                                'specification_type' => 'technical',
                                'sub_section' => $subSection,
                                'title' => $specTitle,
                            ],
                            [
                                'sort_order' => $order++,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]
                        );
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('product_specifications')->truncate();
    }
};
