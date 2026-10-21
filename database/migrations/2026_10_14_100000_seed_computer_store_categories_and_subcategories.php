<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations to seed categories and subcategories.
     */
    public function up(): void
    {
        // 1. Definition of Main Categories with their Subcategories
        $catalog = [
            [
                'name' => 'Laptops',
                'slug' => 'laptops',
                'image' => 'images/categories/laptops.png',
                'description' => 'High-performance gaming, business, and ultrabook laptops.',
                'sort_order' => 1,
                'subcategories' => [],
            ],
            [
                'name' => 'PC Components',
                'slug' => 'pc-components',
                'image' => 'images/categories/PCComponents.png',
                'description' => 'Core hardware, processors, graphics cards, motherboards and internal components.',
                'sort_order' => 2,
                'subcategories' => [
                    ['name' => 'Processors', 'slug' => 'processors', 'image' => 'images/categories/processors.webp'],
                    ['name' => 'Motherboards', 'slug' => 'motherboards', 'image' => 'images/categories/motherboards.webp'],
                    ['name' => 'Coolers', 'slug' => 'coolers', 'image' => 'images/categories/coolers.webp'],
                    ['name' => 'Memory Cards', 'slug' => 'memory', 'image' => 'images/categories/memory.webp'],
                    ['name' => 'SSD', 'slug' => 'ssd', 'image' => 'images/categories/ssd.webp'],
                    ['name' => 'Hard Disk', 'slug' => 'hard-disk', 'image' => 'images/categories/hard-disk.webp'],
                    ['name' => 'Graphic Cards', 'slug' => 'graphic-cards', 'image' => 'images/categories/graphic-cards.webp'],
                    ['name' => 'Power Supply', 'slug' => 'power-supply', 'image' => 'images/categories/power-supply.webp'],
                    ['name' => 'Thermal Paste', 'slug' => 'thermal-paste', 'image' => 'images/categories/thermal-paste.webp'],
                    ['name' => 'PC Cases', 'slug' => 'pc-cases', 'image' => 'images/categories/pc-cases.webp'],
                ],
            ],
            [
                'name' => 'Peripherals',
                'slug' => 'peripherals',
                'image' => 'images/categories/Peripherals.png',
                'description' => 'Gaming and office peripherals, keyboards, mice, headsets, and creator accessories.',
                'sort_order' => 3,
                'subcategories' => [
                    ['name' => 'Headsets', 'slug' => 'headsets', 'image' => 'images/categories/headsets.webp'],
                    ['name' => 'Keyboards', 'slug' => 'keyboards', 'image' => 'images/categories/keyboards.webp'],
                    ['name' => 'Mouse', 'slug' => 'mouse', 'image' => 'images/categories/mouse.webp'],
                    ['name' => 'Mouse Pad', 'slug' => 'mouse-pad', 'image' => 'images/categories/mouse-pad.webp'],
                    ['name' => 'Speakers', 'slug' => 'speakers', 'image' => 'images/categories/speakers.webp'],
                    ['name' => 'Webcam', 'slug' => 'webcam', 'image' => 'images/categories/webcam.webp'],
                    ['name' => 'Converters', 'slug' => 'converters', 'image' => 'images/categories/converters.webp'],
                    ['name' => 'Adapters', 'slug' => 'adapters', 'image' => 'images/categories/adapters.webp'],
                    ['name' => 'Video & Creator Equipments', 'slug' => 'video-creator-equipments', 'image' => 'images/categories/video-creator-equipments.webp'],
                ],
            ],
            [
                'name' => 'Monitors & Displays',
                'slug' => 'monitors-displays',
                'image' => 'images/categories/displays.png',
                'description' => 'High-refresh rate gaming monitors, 4K displays, and dual-monitor accessories.',
                'sort_order' => 4,
                'subcategories' => [],
            ],
            [
                'name' => 'Gaming',
                'slug' => 'gaming',
                'image' => 'images/categories/gaming.png',
                'description' => 'Dedicated gaming equipment, consoles, controllers, and battle station essentials.',
                'sort_order' => 5,
                'subcategories' => [
                    ['name' => 'Laptops', 'slug' => 'gaming-laptops', 'image' => 'images/categories/gaming-laptops.webp'],
                    ['name' => 'Consoles', 'slug' => 'consoles', 'image' => 'images/categories/consoles.webp'],
                    ['name' => 'Controllers', 'slug' => 'controllers', 'image' => 'images/categories/controllers.webp'],
                ],
            ],
            [
                'name' => 'Products',
                'slug' => 'products',
                'image' => 'images/categories/products.png',
                'description' => 'Complete desktop computers, power backup systems, ergonomic desks, and office equipment.',
                'sort_order' => 6,
                'subcategories' => [
                    ['name' => 'Computers', 'slug' => 'computers', 'image' => 'images/categories/computers.webp'],
                    ['name' => 'UPS', 'slug' => 'ups', 'image' => 'images/categories/ups.webp'],
                    ['name' => 'Chairs', 'slug' => 'chairs', 'image' => 'images/categories/chairs.webp'],
                    ['name' => 'Tables', 'slug' => 'tables', 'image' => 'images/categories/tables.webp'],
                    ['name' => 'Power Banks', 'slug' => 'power-banks', 'image' => 'images/categories/power-banks.webp'],
                    ['name' => 'Extension Cards', 'slug' => 'extension-cards', 'image' => 'images/categories/extension-cards.webp'],
                    ['name' => 'Printers', 'slug' => 'printers', 'image' => 'images/categories/printers.webp'],
                    ['name' => 'Toners and Cartridges', 'slug' => 'toners-cartridges', 'image' => 'images/categories/toners-cartridges.webp'],
                    ['name' => 'Scanners', 'slug' => 'scanners', 'image' => 'images/categories/scanners.webp'],
                ],
            ],
            [
                'name' => 'Software',
                'slug' => 'software',
                'image' => 'images/categories/software.png',
                'description' => 'Operating systems, antivirus security, and creative productivity software suites.',
                'sort_order' => 7,
                'subcategories' => [],
            ],
        ];

        $now = now();

        foreach ($catalog as $catData) {
            // Check or insert main category
            $mainCategory = DB::table('categories')->where('slug', $catData['slug'])->first();

            if ($mainCategory) {
                DB::table('categories')->where('id', $mainCategory->id)->update([
                    'name' => $catData['name'],
                    'image' => $catData['image'],
                    'description' => $catData['description'],
                    'sort_order' => $catData['sort_order'],
                    'is_visible' => true,
                    'updated_at' => $now,
                ]);
                $parentId = $mainCategory->id;
            } else {
                $parentId = DB::table('categories')->insertGetId([
                    'name' => $catData['name'],
                    'slug' => $catData['slug'],
                    'parent_id' => null,
                    'image' => $catData['image'],
                    'description' => $catData['description'],
                    'sort_order' => $catData['sort_order'],
                    'is_visible' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Insert subcategories
            $subOrder = 1;
            foreach ($catData['subcategories'] as $subData) {
                $existingSub = DB::table('categories')->where('slug', $subData['slug'])->first();

                if ($existingSub) {
                    DB::table('categories')->where('id', $existingSub->id)->update([
                        'name' => $subData['name'],
                        'parent_id' => $parentId,
                        'image' => $subData['image'],
                        'sort_order' => $subOrder++,
                        'is_visible' => true,
                        'updated_at' => $now,
                    ]);
                } else {
                    DB::table('categories')->insert([
                        'name' => $subData['name'],
                        'slug' => $subData['slug'],
                        'parent_id' => $parentId,
                        'image' => $subData['image'],
                        'description' => "{$subData['name']} under {$catData['name']}",
                        'sort_order' => $subOrder++,
                        'is_visible' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $allSlugs = [
            'laptops', 'pc-components', 'peripherals', 'monitors-displays', 'gaming', 'products', 'software',
            'processors', 'motherboards', 'coolers', 'memory', 'ssd', 'hard-disk', 'graphic-cards', 'power-supply', 'thermal-paste',
            'headsets', 'keyboards', 'mouse', 'mouse-pad', 'speakers', 'webcam', 'converters', 'adapters', 'video-creator-equipments',
            'gaming-laptops', 'consoles', 'controllers',
            'computers', 'ups', 'chairs', 'tables', 'power-banks', 'extension-cards', 'printers', 'toners-cartridges', 'scanners',
        ];

        DB::table('categories')->whereIn('slug', $allSlugs)->delete();
    }
};
