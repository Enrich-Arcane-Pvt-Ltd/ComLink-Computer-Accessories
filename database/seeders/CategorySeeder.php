<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
                    ['name' => 'Processors', 'slug' => 'processors', 'image' => 'images/categories/processors.svg'],
                    ['name' => 'Motherboards', 'slug' => 'motherboards', 'image' => 'images/categories/motherboards.svg'],
                    ['name' => 'Coolers', 'slug' => 'coolers', 'image' => 'images/categories/coolers.svg'],
                    ['name' => 'Memory Cards', 'slug' => 'memory', 'image' => 'images/categories/memory.svg'],
                    ['name' => 'SSD', 'slug' => 'ssd', 'image' => 'images/categories/ssd.svg'],
                    ['name' => 'Hard Disk', 'slug' => 'hard-disk', 'image' => 'images/categories/hard-disk.svg'],
                    ['name' => 'Graphic Cards', 'slug' => 'graphic-cards', 'image' => 'images/categories/graphic-cards.svg'],
                    ['name' => 'Power Supply', 'slug' => 'power-supply', 'image' => 'images/categories/power-supply.svg'],
                    ['name' => 'Thermal Paste', 'slug' => 'thermal-paste', 'image' => 'images/categories/thermal-paste.svg'],
                    ['name' => 'PC Cases', 'slug' => 'pc-cases', 'image' => 'images/categories/pc-cases.svg'],
                ],
            ],
            [
                'name' => 'Peripherals',
                'slug' => 'peripherals',
                'image' => 'images/categories/Peripherals.png',
                'description' => 'Gaming and office peripherals, keyboards, mice, headsets, and creator accessories.',
                'sort_order' => 3,
                'subcategories' => [
                    ['name' => 'Headsets', 'slug' => 'headsets', 'image' => 'images/categories/headsets.svg'],
                    ['name' => 'Keyboards', 'slug' => 'keyboards', 'image' => 'images/categories/keyboards.svg'],
                    ['name' => 'Mouse', 'slug' => 'mouse', 'image' => 'images/categories/mouse.svg'],
                    ['name' => 'Mouse Pad', 'slug' => 'mouse-pad', 'image' => 'images/categories/mouse-pad.svg'],
                    ['name' => 'Speakers', 'slug' => 'speakers', 'image' => 'images/categories/speakers.svg'],
                    ['name' => 'Webcam', 'slug' => 'webcam', 'image' => 'images/categories/webcam.svg'],
                    ['name' => 'Converters', 'slug' => 'converters', 'image' => 'images/categories/converters.svg'],
                    ['name' => 'Adapters', 'slug' => 'adapters', 'image' => 'images/categories/adapters.svg'],
                    ['name' => 'Video & Creator Equipments', 'slug' => 'video-creator-equipments', 'image' => 'images/categories/video-creator-equipments.svg'],
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
                    ['name' => 'Laptops', 'slug' => 'gaming-laptops', 'image' => 'images/categories/gaming-laptops.svg'],
                    ['name' => 'Consoles', 'slug' => 'consoles', 'image' => 'images/categories/consoles.svg'],
                    ['name' => 'Controllers', 'slug' => 'controllers', 'image' => 'images/categories/controllers.svg'],
                ],
            ],
            [
                'name' => 'Products',
                'slug' => 'products',
                'image' => 'images/categories/products.png',
                'description' => 'Complete desktop computers, power backup systems, ergonomic desks, and office equipment.',
                'sort_order' => 6,
                'subcategories' => [
                    ['name' => 'Computers', 'slug' => 'computers', 'image' => 'images/categories/computers.svg'],
                    ['name' => 'UPS', 'slug' => 'ups', 'image' => 'images/categories/ups.svg'],
                    ['name' => 'Chairs', 'slug' => 'chairs', 'image' => 'images/categories/chairs.svg'],
                    ['name' => 'Tables', 'slug' => 'tables', 'image' => 'images/categories/tables.svg'],
                    ['name' => 'Power Banks', 'slug' => 'power-banks', 'image' => 'images/categories/power-banks.svg'],
                    ['name' => 'Extension Cards', 'slug' => 'extension-cards', 'image' => 'images/categories/extension-cards.svg'],
                    ['name' => 'Printers', 'slug' => 'printers', 'image' => 'images/categories/printers.svg'],
                    ['name' => 'Toners and Cartridges', 'slug' => 'toners-cartridges', 'image' => 'images/categories/toners-cartridges.svg'],
                    ['name' => 'Scanners', 'slug' => 'scanners', 'image' => 'images/categories/scanners.svg'],
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

        foreach ($catalog as $catData) {
            $mainCat = Category::updateOrCreate(
                ['slug' => $catData['slug']],
                [
                    'name' => $catData['name'],
                    'parent_id' => null,
                    'image' => $catData['image'],
                    'description' => $catData['description'],
                    'sort_order' => $catData['sort_order'],
                    'is_visible' => true,
                ]
            );

            $subOrder = 1;
            foreach ($catData['subcategories'] as $sub) {
                Category::updateOrCreate(
                    ['slug' => $sub['slug']],
                    [
                        'name' => $sub['name'],
                        'parent_id' => $mainCat->id,
                        'image' => $sub['image'],
                        'description' => "{$sub['name']} under {$catData['name']}",
                        'sort_order' => $subOrder++,
                        'is_visible' => true,
                    ]
                );
            }
        }
    }
}
