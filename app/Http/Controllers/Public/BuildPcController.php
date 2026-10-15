<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BuildPcController extends Controller
{
    /**
     * Display the PC Builder interactive application
     */
    public function index(Request $request): Response
    {
        $categories = $this->getCategoryCatalog();
        $products = $this->getBuilderProducts();
        $templates = $this->getPreBuiltTemplates();

        return Inertia::render('BuildPc/Index', [
            'categories' => $categories,
            'products' => $products,
            'templates' => $templates,
        ]);
    }

    /**
     * Category hierarchy matching CategorySeeder
     */
    protected function getCategoryCatalog(): array
    {
        $dbCats = Category::main()
            ->visible()
            ->orderBy('sort_order', 'asc')
            ->with(['children' => function ($q) {
                $q->visible()->orderBy('sort_order', 'asc');
            }])
            ->get();

        if ($dbCats->isNotEmpty()) {
            return $dbCats->map(function ($cat) {
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'description' => $cat->description,
                    'subcategories' => $cat->children->map(function ($sub) {
                        return [
                            'id' => $sub->id,
                            'name' => $sub->name,
                            'slug' => $sub->slug,
                        ];
                    })->values()->all(),
                ];
            })->values()->all();
        }

        // Fallback matching CategorySeeder
        return [
            [
                'name' => 'PC Components',
                'slug' => 'pc-components',
                'subcategories' => [
                    ['name' => 'Processors', 'slug' => 'processors'],
                    ['name' => 'Motherboards', 'slug' => 'motherboards'],
                    ['name' => 'Memory Cards', 'slug' => 'memory'],
                    ['name' => 'Graphic Cards', 'slug' => 'graphic-cards'],
                    ['name' => 'SSD', 'slug' => 'ssd'],
                    ['name' => 'Hard Disk', 'slug' => 'hard-disk'],
                    ['name' => 'Power Supply', 'slug' => 'power-supply'],
                    ['name' => 'Coolers', 'slug' => 'coolers'],
                    ['name' => 'PC Cases', 'slug' => 'pc-cases'],
                    ['name' => 'Thermal Paste', 'slug' => 'thermal-paste'],
                ],
            ],
            [
                'name' => 'Peripherals',
                'slug' => 'peripherals',
                'subcategories' => [
                    ['name' => 'Keyboards', 'slug' => 'keyboards'],
                    ['name' => 'Mouse', 'slug' => 'mouse'],
                    ['name' => 'Mouse Pad', 'slug' => 'mouse-pad'],
                    ['name' => 'Headsets', 'slug' => 'headsets'],
                    ['name' => 'Speakers', 'slug' => 'speakers'],
                    ['name' => 'Webcam', 'slug' => 'webcam'],
                    ['name' => 'Converters', 'slug' => 'converters'],
                    ['name' => 'Adapters', 'slug' => 'adapters'],
                    ['name' => 'Video & Creator Equipments', 'slug' => 'video-creator-equipments'],
                ],
            ],
            [
                'name' => 'Monitors & Displays',
                'slug' => 'monitors-displays',
                'subcategories' => [],
            ],
            [
                'name' => 'Products',
                'slug' => 'products',
                'subcategories' => [
                    ['name' => 'Computers', 'slug' => 'computers'],
                    ['name' => 'UPS', 'slug' => 'ups'],
                    ['name' => 'Chairs', 'slug' => 'chairs'],
                    ['name' => 'Tables', 'slug' => 'tables'],
                    ['name' => 'Power Banks', 'slug' => 'power-banks'],
                    ['name' => 'Extension Cards', 'slug' => 'extension-cards'],
                    ['name' => 'Printers', 'slug' => 'printers'],
                    ['name' => 'Toners and Cartridges', 'slug' => 'toners-cartridges'],
                    ['name' => 'Scanners', 'slug' => 'scanners'],
                ],
            ],
            [
                'name' => 'Gaming',
                'slug' => 'gaming',
                'subcategories' => [
                    ['name' => 'Laptops', 'slug' => 'gaming-laptops'],
                    ['name' => 'Consoles', 'slug' => 'consoles'],
                    ['name' => 'Controllers', 'slug' => 'controllers'],
                ],
            ],
            [
                'name' => 'Laptops',
                'slug' => 'laptops',
                'subcategories' => [],
            ],
            [
                'name' => 'Software',
                'slug' => 'software',
                'subcategories' => [],
            ],
        ];
    }

    /**
     * Get products structured for the PC builder slots
     */
    protected function getBuilderProducts(): array
    {
        // Realistic catalog items for each PC component category
        return [
            // ==========================================
            // 1. PROCESSORS (CPU)
            // ==========================================
            [
                'id' => 1001,
                'name' => 'AMD Ryzen 7 7800X3D 8-Core 16-Thread Gaming Processor',
                'slot' => 'processors',
                'category' => 'PC Components',
                'subCategory' => 'Processors',
                'brand' => 'AMD',
                'chipset' => 'AMD',
                'socket' => 'AM5',
                'wattage' => 120,
                'price' => 148500.00,
                'originalPrice' => 155000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Cores / Threads' => '8C / 16T',
                    'Base / Boost' => '4.2 GHz / 5.0 GHz',
                    'L3 Cache' => '96MB 3D V-Cache',
                    'Socket' => 'AM5',
                    'TDP' => '120W',
                ],
            ],
            [
                'id' => 1002,
                'name' => 'AMD Ryzen 5 7600X 6-Core 12-Thread Unlocked Desktop Processor',
                'slot' => 'processors',
                'category' => 'PC Components',
                'subCategory' => 'Processors',
                'brand' => 'AMD',
                'chipset' => 'AMD',
                'socket' => 'AM5',
                'wattage' => 105,
                'price' => 79500.00,
                'originalPrice' => 84000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Cores / Threads' => '6C / 12T',
                    'Base / Boost' => '4.7 GHz / 5.3 GHz',
                    'L3 Cache' => '32MB',
                    'Socket' => 'AM5',
                    'TDP' => '105W',
                ],
            ],
            [
                'id' => 1003,
                'name' => 'AMD Ryzen 9 7950X3D 16-Core 32-Thread Flagship Gaming Processor',
                'slot' => 'processors',
                'category' => 'PC Components',
                'subCategory' => 'Processors',
                'brand' => 'AMD',
                'chipset' => 'AMD',
                'socket' => 'AM5',
                'wattage' => 120,
                'price' => 225000.00,
                'originalPrice' => 239000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Cores / Threads' => '16C / 32T',
                    'Base / Boost' => '4.2 GHz / 5.7 GHz',
                    'L3 Cache' => '128MB 3D V-Cache',
                    'Socket' => 'AM5',
                    'TDP' => '120W',
                ],
            ],
            [
                'id' => 1004,
                'name' => 'Intel Core i9-14900K 24-Core 6.0GHz LGA1700 Desktop Processor',
                'slot' => 'processors',
                'category' => 'PC Components',
                'subCategory' => 'Processors',
                'brand' => 'Intel',
                'chipset' => 'Intel',
                'socket' => 'LGA1700',
                'wattage' => 253,
                'price' => 189000.00,
                'originalPrice' => 199000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Cores / Threads' => '24C (8P+16E) / 32T',
                    'Max Turbo Frequency' => '6.0 GHz',
                    'Cache' => '36MB Smart Cache',
                    'Socket' => 'LGA1700',
                    'TDP' => '253W Max',
                ],
            ],
            [
                'id' => 1005,
                'name' => 'Intel Core i7-14700K 20-Core LGA1700 Turbo 5.6GHz Gaming Processor',
                'slot' => 'processors',
                'category' => 'PC Components',
                'subCategory' => 'Processors',
                'brand' => 'Intel',
                'chipset' => 'Intel',
                'socket' => 'LGA1700',
                'wattage' => 253,
                'price' => 139000.00,
                'originalPrice' => 146000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Cores / Threads' => '20C (8P+12E) / 28T',
                    'Max Turbo' => '5.6 GHz',
                    'Cache' => '33MB Smart Cache',
                    'Socket' => 'LGA1700',
                    'TDP' => '253W Max',
                ],
            ],
            [
                'id' => 1006,
                'name' => 'Intel Core i5-14600K 14-Core 5.3GHz LGA1700 Unlocked Processor',
                'slot' => 'processors',
                'category' => 'PC Components',
                'subCategory' => 'Processors',
                'brand' => 'Intel',
                'chipset' => 'Intel',
                'socket' => 'LGA1700',
                'wattage' => 181,
                'price' => 98500.00,
                'originalPrice' => 104000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Cores / Threads' => '14C (6P+8E) / 20T',
                    'Max Turbo' => '5.3 GHz',
                    'Cache' => '24MB Smart Cache',
                    'Socket' => 'LGA1700',
                    'TDP' => '181W Max',
                ],
            ],

            // ==========================================
            // 2. MOTHERBOARDS (MOBO)
            // ==========================================
            [
                'id' => 1010,
                'name' => 'MSI MAG B650 TOMAHAWK WIFI AM5 ATX Gaming Motherboard',
                'slot' => 'motherboards',
                'category' => 'PC Components',
                'subCategory' => 'Motherboards',
                'brand' => 'MSI',
                'chipset' => 'AMD',
                'socket' => 'AM5',
                'wattage' => 45,
                'price' => 78500.00,
                'originalPrice' => 84000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Form Factor' => 'ATX',
                    'Socket' => 'AM5',
                    'RAM Support' => 'DDR5 7600+ MHz (OC)',
                    'PCIe Slots' => 'PCIe 4.0 x16',
                    'M.2 Slots' => '3x M.2 Gen4',
                    'Connectivity' => 'Wi-Fi 6E + 2.5G LAN',
                ],
            ],
            [
                'id' => 1011,
                'name' => 'ASUS ROG STRIX B650-A GAMING WIFI AM5 White ATX Motherboard',
                'slot' => 'motherboards',
                'category' => 'PC Components',
                'subCategory' => 'Motherboards',
                'brand' => 'ASUS',
                'chipset' => 'AMD',
                'socket' => 'AM5',
                'wattage' => 45,
                'price' => 89500.00,
                'originalPrice' => 95000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Form Factor' => 'ATX',
                    'Socket' => 'AM5',
                    'RAM Support' => 'DDR5 7600+ MHz',
                    'M.2 Slots' => '3x M.2 (1x Gen5, 2x Gen4)',
                    'Features' => 'Aura Sync RGB, WiFi 6E',
                ],
            ],
            [
                'id' => 1012,
                'name' => 'Gigabyte B650M GAMING X AX AM5 Micro-ATX Motherboard',
                'slot' => 'motherboards',
                'category' => 'PC Components',
                'subCategory' => 'Motherboards',
                'brand' => 'Gigabyte',
                'chipset' => 'AMD',
                'socket' => 'AM5',
                'wattage' => 35,
                'price' => 58000.00,
                'originalPrice' => 62000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Form Factor' => 'Micro-ATX',
                    'Socket' => 'AM5',
                    'RAM Support' => 'DDR5 6400+ MHz',
                    'M.2 Slots' => '2x M.2 Gen4',
                    'Connectivity' => 'Wi-Fi 6E + 2.5G LAN',
                ],
            ],
            [
                'id' => 1013,
                'name' => 'ASUS ROG MAXIMUS Z790 HERO LGA1700 ATX Flagship Motherboard',
                'slot' => 'motherboards',
                'category' => 'PC Components',
                'subCategory' => 'Motherboards',
                'brand' => 'ASUS',
                'chipset' => 'Intel',
                'socket' => 'LGA1700',
                'wattage' => 65,
                'price' => 195000.00,
                'originalPrice' => 210000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Form Factor' => 'ATX',
                    'Socket' => 'LGA1700',
                    'Power Stages' => '20+1 90A',
                    'PCIe Slots' => '2x PCIe 5.0 x16',
                    'M.2 Slots' => '5x M.2 slots (1x Gen5)',
                    'Ports' => 'Dual Thunderbolt 4',
                ],
            ],
            [
                'id' => 1014,
                'name' => 'MSI PRO Z790-P WIFI DDR5 LGA1700 ATX Gaming Motherboard',
                'slot' => 'motherboards',
                'category' => 'PC Components',
                'subCategory' => 'Motherboards',
                'brand' => 'MSI',
                'chipset' => 'Intel',
                'socket' => 'LGA1700',
                'wattage' => 45,
                'price' => 84000.00,
                'originalPrice' => 89000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Form Factor' => 'ATX',
                    'Socket' => 'LGA1700',
                    'RAM Support' => 'DDR5 7200+ MHz',
                    'PCIe Slots' => 'PCIe 5.0 x16',
                    'M.2 Slots' => '4x M.2 Gen4',
                    'Connectivity' => 'Wi-Fi 6E, 2.5G LAN',
                ],
            ],
            [
                'id' => 1015,
                'name' => 'ASUS PRIME B760M-A WIFI D4 Micro-ATX Motherboard',
                'slot' => 'motherboards',
                'category' => 'PC Components',
                'subCategory' => 'Motherboards',
                'brand' => 'ASUS',
                'chipset' => 'Intel',
                'socket' => 'LGA1700',
                'wattage' => 35,
                'price' => 54500.00,
                'originalPrice' => 58000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Form Factor' => 'Micro-ATX',
                    'Socket' => 'LGA1700',
                    'RAM Support' => 'DDR5 7200+ MHz',
                    'M.2 Slots' => '2x M.2 Gen4',
                    'Connectivity' => 'Wi-Fi 6, 2.5G LAN',
                ],
            ],

            // ==========================================
            // 3. MEMORY (RAM)
            // ==========================================
            [
                'id' => 1020,
                'name' => 'Corsair Vengeance RGB DDR5 32GB (2x16GB) 6000MHz CL30 Black',
                'slot' => 'memory',
                'category' => 'PC Components',
                'subCategory' => 'Memory Cards',
                'brand' => 'Corsair',
                'wattage' => 15,
                'price' => 44500.00,
                'originalPrice' => 48000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Capacity' => '32GB (2 x 16GB)',
                    'Speed' => 'DDR5 6000 MT/s',
                    'Timing' => 'CL30-36-36-76',
                    'RGB' => 'Dynamic Ten-Zone RGB',
                    'Profile' => 'AMD EXPO & Intel XMP 3.0',
                ],
            ],
            [
                'id' => 1021,
                'name' => 'G.SKILL Trident Z5 RGB 32GB (2x16GB) DDR5 6400MHz CL32 Silver',
                'slot' => 'memory',
                'category' => 'PC Components',
                'subCategory' => 'Memory Cards',
                'brand' => 'G.SKILL',
                'wattage' => 15,
                'price' => 49500.00,
                'originalPrice' => 53000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Capacity' => '32GB (2 x 16GB)',
                    'Speed' => 'DDR5 6400 MT/s',
                    'Timing' => 'CL32-39-39-102',
                    'Design' => 'Brushed Aluminum Heatspreader',
                ],
            ],
            [
                'id' => 1022,
                'name' => 'Corsair Dominator Titanium RGB 64GB (2x32GB) DDR5 6000MHz CL30',
                'slot' => 'memory',
                'category' => 'PC Components',
                'subCategory' => 'Memory Cards',
                'brand' => 'Corsair',
                'wattage' => 20,
                'price' => 92000.00,
                'originalPrice' => 98000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Capacity' => '64GB (2 x 32GB)',
                    'Speed' => 'DDR5 6000 MT/s',
                    'Timing' => 'CL30-36-36-76',
                    'Profile' => 'Intel XMP 3.0 / EXPO',
                ],
            ],
            [
                'id' => 1023,
                'name' => 'Kingston FURY Beast RGB 16GB (2x8GB) DDR5 5600MHz CL36',
                'slot' => 'memory',
                'category' => 'PC Components',
                'subCategory' => 'Memory Cards',
                'brand' => 'Kingston',
                'wattage' => 10,
                'price' => 26500.00,
                'originalPrice' => 29000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Capacity' => '16GB (2 x 8GB)',
                    'Speed' => 'DDR5 5600 MT/s',
                    'Timing' => 'CL36',
                    'Profile' => 'Intel XMP 3.0 / AMD EXPO',
                ],
            ],

            // ==========================================
            // 4. GRAPHICS CARDS (GPU)
            // ==========================================
            [
                'id' => 1030,
                'name' => 'ASUS ROG Strix GeForce RTX 4090 OC Edition 24GB GDDR6X',
                'slot' => 'graphic-cards',
                'category' => 'PC Components',
                'subCategory' => 'Graphic Cards',
                'brand' => 'ASUS',
                'wattage' => 450,
                'price' => 695000.00,
                'originalPrice' => 725000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'VRAM' => '24GB GDDR6X',
                    'Clock' => '2640 MHz (OC Mode)',
                    'Memory Bus' => '384-bit',
                    'Outputs' => '2x HDMI 2.1a, 3x DisplayPort 1.4a',
                    'Recommended PSU' => '1000W',
                ],
            ],
            [
                'id' => 1031,
                'name' => 'MSI GeForce RTX 4080 SUPER 16GB GAMING X TRIO GDDR6X',
                'slot' => 'graphic-cards',
                'category' => 'PC Components',
                'subCategory' => 'Graphic Cards',
                'brand' => 'MSI',
                'wattage' => 320,
                'price' => 365000.00,
                'originalPrice' => 385000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'VRAM' => '16GB GDDR6X',
                    'Clock' => '2610 MHz',
                    'Memory Bus' => '256-bit',
                    'Cooling' => 'TRI FROZR 3 Thermal Design',
                    'Recommended PSU' => '850W',
                ],
            ],
            [
                'id' => 1032,
                'name' => 'ASUS TUF Gaming GeForce RTX 4070 Ti SUPER 16GB GDDR6X OC',
                'slot' => 'graphic-cards',
                'category' => 'PC Components',
                'subCategory' => 'Graphic Cards',
                'brand' => 'ASUS',
                'wattage' => 285,
                'price' => 275000.00,
                'originalPrice' => 289000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'VRAM' => '16GB GDDR6X',
                    'Clock' => '2670 MHz',
                    'Cooling' => 'Axial-tech Fans, Metal Exoskeleton',
                    'Recommended PSU' => '750W',
                ],
            ],
            [
                'id' => 1033,
                'name' => 'Gigabyte GeForce RTX 4060 Ti Gaming OC 8GB GDDR6',
                'slot' => 'graphic-cards',
                'category' => 'PC Components',
                'subCategory' => 'Graphic Cards',
                'brand' => 'Gigabyte',
                'wattage' => 160,
                'price' => 149000.00,
                'originalPrice' => 158000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'VRAM' => '8GB GDDR6',
                    'Clock' => '2580 MHz',
                    'Cooling' => 'WINDFORCE 3X Fans',
                    'Recommended PSU' => '500W',
                ],
            ],
            [
                'id' => 1034,
                'name' => 'ASUS Dual GeForce RTX 4060 EVO OC 8GB GDDR6 Compact Card',
                'slot' => 'graphic-cards',
                'category' => 'PC Components',
                'subCategory' => 'Graphic Cards',
                'brand' => 'ASUS',
                'wattage' => 115,
                'price' => 115000.00,
                'originalPrice' => 122000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'VRAM' => '8GB GDDR6',
                    'Clock' => '2535 MHz (OC Mode)',
                    'Form Factor' => '2.5-slot Design',
                    'Recommended PSU' => '550W',
                ],
            ],
            [
                'id' => 1035,
                'name' => 'Sapphire PULSE AMD Radeon RX 7800 XT 16GB GDDR6 Graphics Card',
                'slot' => 'graphic-cards',
                'category' => 'PC Components',
                'subCategory' => 'Graphic Cards',
                'brand' => 'Sapphire',
                'wattage' => 263,
                'price' => 185000.00,
                'originalPrice' => 195000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'VRAM' => '16GB GDDR6',
                    'Engine Clock' => 'Up to 2430 MHz Boost',
                    'Stream Processors' => '3840 Units',
                    'Recommended PSU' => '700W',
                ],
            ],

            // ==========================================
            // 5. SSD & NVME (PRIMARY STORAGE)
            // ==========================================
            [
                'id' => 1040,
                'name' => 'Samsung 990 PRO 2TB PCIe Gen4 NVMe M.2 Internal SSD',
                'slot' => 'ssd',
                'category' => 'PC Components',
                'subCategory' => 'SSD',
                'brand' => 'Samsung',
                'wattage' => 9,
                'price' => 58500.00,
                'originalPrice' => 64000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Capacity' => '2TB',
                    'Read Speed' => 'Up to 7,450 MB/s',
                    'Write Speed' => 'Up to 6,900 MB/s',
                    'Interface' => 'PCIe Gen 4.0 x4, NVMe 2.0',
                    'Endurance' => '1200 TBW',
                ],
            ],
            [
                'id' => 1041,
                'name' => 'Samsung 980 PRO 1TB PCIe Gen4 NVMe M.2 High-Speed SSD',
                'slot' => 'ssd',
                'category' => 'PC Components',
                'subCategory' => 'SSD',
                'brand' => 'Samsung',
                'wattage' => 8,
                'price' => 36500.00,
                'originalPrice' => 39500.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Capacity' => '1TB',
                    'Read Speed' => 'Up to 7,000 MB/s',
                    'Write Speed' => 'Up to 5,000 MB/s',
                    'Form Factor' => 'M.2 2280',
                ],
            ],
            [
                'id' => 1042,
                'name' => 'Kingston KC3000 2TB PCIe 4.0 NVMe M.2 Solid State Drive',
                'slot' => 'ssd',
                'category' => 'PC Components',
                'subCategory' => 'SSD',
                'brand' => 'Kingston',
                'wattage' => 9,
                'price' => 52000.00,
                'originalPrice' => 56000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Capacity' => '2TB',
                    'Read / Write' => '7,000 / 7,000 MB/s',
                    'Controller' => 'Phison E18',
                ],
            ],
            [
                'id' => 1043,
                'name' => 'Crucial P3 Plus 1TB PCIe 4.0 3D NAND NVMe M.2 SSD',
                'slot' => 'ssd',
                'category' => 'PC Components',
                'subCategory' => 'SSD',
                'brand' => 'Crucial',
                'wattage' => 6,
                'price' => 24500.00,
                'originalPrice' => 27000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Capacity' => '1TB',
                    'Read Speed' => '5,000 MB/s',
                    'Write Speed' => '4,200 MB/s',
                ],
            ],

            // ==========================================
            // 6. HARD DISK (SECONDARY STORAGE)
            // ==========================================
            [
                'id' => 1050,
                'name' => 'Seagate BarraCuda 2TB 7200 RPM 256MB Cache SATA 6Gb/s 3.5" HDD',
                'slot' => 'hard-disk',
                'category' => 'PC Components',
                'subCategory' => 'Hard Disk',
                'brand' => 'Seagate',
                'wattage' => 15,
                'price' => 21500.00,
                'originalPrice' => 23500.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Capacity' => '2TB',
                    'Interface' => 'SATA 6.0Gb/s',
                    'RPM' => '7200 RPM',
                    'Cache' => '256MB',
                ],
            ],
            [
                'id' => 1051,
                'name' => 'Western Digital WD Blue 4TB 5400 RPM 256MB Cache 3.5" Internal HDD',
                'slot' => 'hard-disk',
                'category' => 'PC Components',
                'subCategory' => 'Hard Disk',
                'brand' => 'Western Digital',
                'wattage' => 15,
                'price' => 34500.00,
                'originalPrice' => 38000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Capacity' => '4TB',
                    'Form Factor' => '3.5-inch',
                    'Interface' => 'SATA 6 Gb/s',
                ],
            ],
            [
                'id' => 1052,
                'name' => 'Seagate IronWolf 8TB NAS 7200 RPM 256MB Cache 3.5" High-Reliability HDD',
                'slot' => 'hard-disk',
                'category' => 'PC Components',
                'subCategory' => 'Hard Disk',
                'brand' => 'Seagate',
                'wattage' => 20,
                'price' => 74500.00,
                'originalPrice' => 82000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Capacity' => '8TB',
                    'Workload' => '180TB/year Workload Rate',
                    'RPM' => '7200 RPM',
                ],
            ],

            // ==========================================
            // 7. POWER SUPPLY (PSU)
            // ==========================================
            [
                'id' => 1060,
                'name' => 'Corsair RM850x 850W 80 PLUS Gold Fully Modular ATX Power Supply',
                'slot' => 'power-supply',
                'category' => 'PC Components',
                'subCategory' => 'Power Supply',
                'brand' => 'Corsair',
                'wattage' => 850,
                'price' => 48500.00,
                'originalPrice' => 52000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Wattage Output' => '850W',
                    'Efficiency' => '80 PLUS Gold Certified',
                    'Modularity' => 'Fully Modular Cables',
                    'Fan' => '135mm Magnetic Levitation Fan',
                    'ATX Spec' => 'ATX 3.0 / PCIe 5.0 Ready',
                ],
            ],
            [
                'id' => 1061,
                'name' => 'ASUS ROG Thor 1000W Platinum II Fully Modular PSU with OLED Power Display',
                'slot' => 'power-supply',
                'category' => 'PC Components',
                'subCategory' => 'Power Supply',
                'brand' => 'ASUS',
                'wattage' => 1000,
                'price' => 115000.00,
                'originalPrice' => 125000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Wattage Output' => '1000W',
                    'Efficiency' => '80 PLUS Platinum Certified',
                    'OLED Display' => 'Real-time Wattage Display',
                    'Cables' => 'PCIe 5.0 16-pin 12VHPWR Included',
                ],
            ],
            [
                'id' => 1062,
                'name' => 'MSI MAG A750GL PCIE5 750W 80 PLUS Gold Fully Modular Power Supply',
                'slot' => 'power-supply',
                'category' => 'PC Components',
                'subCategory' => 'Power Supply',
                'brand' => 'MSI',
                'wattage' => 750,
                'price' => 38500.00,
                'originalPrice' => 42000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Wattage Output' => '750W',
                    'Efficiency' => '80 PLUS Gold',
                    'ATX Spec' => 'ATX 3.0 & PCIe 5.0 Ready',
                    'Modularity' => 'Full Modular flat cables',
                ],
            ],
            [
                'id' => 1063,
                'name' => 'Corsair CV650 650W 80 PLUS Bronze Compact ATX Power Supply',
                'slot' => 'power-supply',
                'category' => 'PC Components',
                'subCategory' => 'Power Supply',
                'brand' => 'Corsair',
                'wattage' => 650,
                'price' => 22500.00,
                'originalPrice' => 25000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Wattage Output' => '650W',
                    'Efficiency' => '80 PLUS Bronze',
                    'Fan' => '120mm Low-noise thermal fan',
                ],
            ],

            // ==========================================
            // 8. COOLERS (CPU COOLER)
            // ==========================================
            [
                'id' => 1070,
                'name' => 'NZXT Kraken Elite 360 RGB LCD All-In-One Liquid CPU Cooler Black',
                'slot' => 'coolers',
                'category' => 'PC Components',
                'subCategory' => 'Coolers',
                'brand' => 'NZXT',
                'wattage' => 25,
                'price' => 88500.00,
                'originalPrice' => 96000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Radiator Size' => '360mm Aluminum',
                    'Display' => '2.36" Wide-Angle LCD (640x640 60Hz)',
                    'Fans' => '3x F120 RGB Core Fans',
                    'Sockets' => 'Intel LGA 1700/1200 & AMD AM5/AM4',
                ],
            ],
            [
                'id' => 1071,
                'name' => 'Corsair iCUE LINK H150i RGB 360mm High-Performance Liquid CPU Cooler',
                'slot' => 'coolers',
                'category' => 'PC Components',
                'subCategory' => 'Coolers',
                'brand' => 'Corsair',
                'wattage' => 25,
                'price' => 74500.00,
                'originalPrice' => 79000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Radiator Size' => '360mm',
                    'Ecosystem' => 'iCUE LINK Single Cable Design',
                    'Fans' => '3x QX120 RGB Magnetic Fans',
                ],
            ],
            [
                'id' => 1072,
                'name' => 'Thermalright Peerless Assassin 120 SE ARGB Dual Tower CPU Air Cooler',
                'slot' => 'coolers',
                'category' => 'PC Components',
                'subCategory' => 'Coolers',
                'brand' => 'Thermalright',
                'wattage' => 10,
                'price' => 18500.00,
                'originalPrice' => 21000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Type' => 'Dual Tower Air Cooler',
                    'Heatpipes' => '6x 6mm AGHP Heatpipes',
                    'Fans' => '2x 120mm TL-C12C-S PWM Fans',
                    'Compatibility' => 'LGA 1700/1200, AM5/AM4',
                ],
            ],
            [
                'id' => 1073,
                'name' => 'DeepCool AK620 High-Performance Dual-Tower Air Cooler with 6 Heatpipes',
                'slot' => 'coolers',
                'category' => 'PC Components',
                'subCategory' => 'Coolers',
                'brand' => 'DeepCool',
                'wattage' => 10,
                'price' => 23500.00,
                'originalPrice' => 26000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Type' => 'Dual Tower Air Cooler',
                    'TDP Capacity' => '260W Heat Dissipation',
                    'Design' => 'Precision Nickel-plated Copper Base',
                ],
            ],

            // ==========================================
            // 9. PC CASE (CHASSIS)
            // ==========================================
            [
                'id' => 1080,
                'name' => 'Lian Li O11 Dynamic EVO RGB Dual-Chamber Tempered Glass ATX Mid Tower Case',
                'slot' => 'pc-cases',
                'category' => 'PC Components',
                'subCategory' => 'PC Cases',
                'brand' => 'Lian Li',
                'wattage' => 0,
                'price' => 54500.00,
                'originalPrice' => 59000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Form Factor' => 'Dual-Chamber Mid-Tower',
                    'Motherboard Support' => 'E-ATX / ATX / Micro-ATX / Mini-ITX',
                    'GPU Clearance' => 'Up to 455mm',
                    'Radiator Support' => 'Top, Side & Bottom 360mm',
                ],
            ],
            [
                'id' => 1081,
                'name' => 'NZXT H9 Flow Dual-Chamber Panoramic Tempered Glass ATX Case Black',
                'slot' => 'pc-cases',
                'category' => 'PC Components',
                'subCategory' => 'PC Cases',
                'brand' => 'NZXT',
                'wattage' => 0,
                'price' => 52000.00,
                'originalPrice' => 56000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Form Factor' => 'Dual-Chamber ATX Mid-Tower',
                    'Glass' => 'Seamless Glass Corner Panel',
                    'Included Fans' => '4x 120mm Quiet Airflow Fans',
                ],
            ],
            [
                'id' => 1082,
                'name' => 'Corsair 4000D AIRFLOW Tempered Glass High-Airflow Mid-Tower Case',
                'slot' => 'pc-cases',
                'category' => 'PC Components',
                'subCategory' => 'PC Cases',
                'brand' => 'Corsair',
                'wattage' => 0,
                'price' => 31500.00,
                'originalPrice' => 35000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Front Panel' => 'Optimized Steel Airflow Channel',
                    'Cable Management' => 'RapidRoute 25mm Channel',
                    'Fans' => '2x 120mm AirGuide Fans Included',
                ],
            ],
            [
                'id' => 1083,
                'name' => 'Montech AIR 903 MAX High Airflow ATX Mid Tower Case with 4x 140mm ARGB Fans',
                'slot' => 'pc-cases',
                'category' => 'PC Components',
                'subCategory' => 'PC Cases',
                'brand' => 'Montech',
                'wattage' => 0,
                'price' => 26500.00,
                'originalPrice' => 29500.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Fans Included' => '4x 140mm PWM Fans (3x ARGB front + 1x rear)',
                    'Radiator' => 'Up to 360mm top and front',
                ],
            ],

            // ==========================================
            // 10. FANS & THERMAL PASTE
            // ==========================================
            [
                'id' => 1090,
                'name' => 'Arctic MX-6 High-Performance Carbon-Based Thermal Paste 4g Tube',
                'slot' => 'fans',
                'category' => 'PC Components',
                'subCategory' => 'Thermal Paste',
                'brand' => 'Arctic',
                'wattage' => 0,
                'price' => 4200.00,
                'originalPrice' => 4800.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Thermal Conductivity' => 'High Viscosity Carbon Filler',
                    'Weight' => '4 Grams with Applicator Spatula',
                    'Conductivity' => 'Non-Conductive, Non-Capacitive',
                ],
            ],
            [
                'id' => 1091,
                'name' => 'Lian Li UNI FAN SL-INFINITY 120 RGB Triple Pack with Controller Black',
                'slot' => 'fans',
                'category' => 'PC Components',
                'subCategory' => 'Thermal Paste',
                'brand' => 'Lian Li',
                'wattage' => 15,
                'price' => 34500.00,
                'originalPrice' => 38000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Fan Size' => '3x 120mm Modular Interlocking Fans',
                    'Lighting' => 'Infinity Mirror 40 LEDs per fan',
                    'RPM' => '200 ~ 2100 RPM PWM',
                ],
            ],
            [
                'id' => 1092,
                'name' => 'Thermal Grizzly Kryonaut Extreme Ultra-Performance Thermal Grease 2g',
                'slot' => 'fans',
                'category' => 'PC Components',
                'subCategory' => 'Thermal Paste',
                'brand' => 'Thermal Grizzly',
                'wattage' => 0,
                'price' => 6900.00,
                'originalPrice' => 7500.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Thermal Conductivity' => '14.2 W/mK',
                    'Viscosity' => '130–180 Pas',
                    'Application' => 'Extreme Overclocking & Delidding',
                ],
            ],

            // ==========================================
            // 11. EXTRA SSD & NVME (SECONDARY)
            // ==========================================
            [
                'id' => 1095,
                'name' => 'Kingston NV2 1TB PCIe 4.0 NVMe M.2 Fast Secondary Internal SSD',
                'slot' => 'extra-ssd',
                'category' => 'PC Components',
                'subCategory' => 'SSD',
                'brand' => 'Kingston',
                'wattage' => 5,
                'price' => 19500.00,
                'originalPrice' => 22000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Capacity' => '1TB',
                    'Speeds' => '3,500 / 2,100 MB/s',
                    'Form Factor' => 'Single-sided M.2 2280',
                ],
            ],
            [
                'id' => 1096,
                'name' => 'Samsung 870 EVO 1TB 2.5" SATA III Internal Storage SSD',
                'slot' => 'extra-ssd',
                'category' => 'PC Components',
                'subCategory' => 'SSD',
                'brand' => 'Samsung',
                'wattage' => 4,
                'price' => 28500.00,
                'originalPrice' => 31000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Capacity' => '1TB',
                    'Speeds' => '560 / 530 MB/s Sequential',
                    'Form Factor' => 'Standard 2.5" SATA',
                ],
            ],

            // ==========================================
            // 12. ACCESSORIES: MONITORS
            // ==========================================
            [
                'id' => 2001,
                'name' => 'Samsung Odyssey OLED G9 49" Curved Dual QHD 240Hz 0.03ms Gaming Monitor',
                'slot' => 'monitors-displays',
                'category' => 'Monitors & Displays',
                'subCategory' => 'Monitors & Displays',
                'brand' => 'Samsung',
                'wattage' => 80,
                'price' => 545000.00,
                'originalPrice' => 590000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Panel' => '49" 1800R Curved QD-OLED',
                    'Resolution' => 'Dual QHD (5120 x 1440)',
                    'Refresh Rate' => '240Hz, 0.03ms (GtG)',
                    'Features' => 'FreeSync Premium Pro, DisplayHDR True Black 400',
                ],
            ],
            [
                'id' => 2002,
                'name' => 'LG UltraGear 27" QHD Nano IPS 180Hz 1ms Gaming Monitor 27GS85Q',
                'slot' => 'monitors-displays',
                'category' => 'Monitors & Displays',
                'subCategory' => 'Monitors & Displays',
                'brand' => 'LG',
                'wattage' => 45,
                'price' => 95000.00,
                'originalPrice' => 105000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Panel' => '27" Nano IPS QHD (2560 x 1440)',
                    'Refresh Rate' => '180Hz OC, 1ms Response Time',
                    'Color' => 'DCI-P3 98% with VESA DisplayHDR 400',
                ],
            ],

            // ==========================================
            // 13. ACCESSORIES: KEYBOARDS
            // ==========================================
            [
                'id' => 2010,
                'name' => 'Redragon K552 Kumara RGB Mechanical Gaming Keyboard Blue Switches',
                'slot' => 'keyboards',
                'category' => 'Peripherals',
                'subCategory' => 'Keyboards',
                'brand' => 'Redragon',
                'wattage' => 2,
                'price' => 14500.00,
                'originalPrice' => 16500.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Switch Type' => 'Custom Clicky Blue Switches',
                    'Layout' => 'Tenkeyless (TKL) 87 Keys',
                    'Lighting' => 'Full RGB Backlighting',
                ],
            ],
            [
                'id' => 2011,
                'name' => 'Corsair K70 RGB PRO Mechanical Gaming Keyboard Cherry MX Red Switches',
                'slot' => 'keyboards',
                'category' => 'Peripherals',
                'subCategory' => 'Keyboards',
                'brand' => 'Corsair',
                'wattage' => 3,
                'price' => 46500.00,
                'originalPrice' => 52000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Switches' => '100% Cherry MX Red Linear',
                    'Polling Rate' => '8,000Hz Hyper-Polling AXON',
                    'Keycaps' => 'Double-Shot PBT Keycaps',
                ],
            ],

            // ==========================================
            // 14. ACCESSORIES: MICE
            // ==========================================
            [
                'id' => 2020,
                'name' => 'Logitech G502 HERO High-Performance RGB Gaming Mouse 25K Sensor',
                'slot' => 'mouse',
                'category' => 'Peripherals',
                'subCategory' => 'Mouse',
                'brand' => 'Logitech',
                'wattage' => 2,
                'price' => 16500.00,
                'originalPrice' => 19000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Sensor' => 'HERO 25K DPI Sub-Micron Sensor',
                    'Buttons' => '11 Programmable Buttons',
                    'Weights' => '5x 3.6g Adjustable Weights',
                ],
            ],
            [
                'id' => 2021,
                'name' => 'Razer DeathAdder V3 Pro Wireless Ergonomic Esports Gaming Mouse 30K',
                'slot' => 'mouse',
                'category' => 'Peripherals',
                'subCategory' => 'Mouse',
                'brand' => 'Razer',
                'wattage' => 2,
                'price' => 42000.00,
                'originalPrice' => 46000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Weight' => 'Ultra-lightweight 63g',
                    'Sensor' => 'Focus Pro 30K Optical Sensor',
                    'Battery Life' => 'Up to 90 Hours',
                ],
            ],

            // ==========================================
            // 15. ACCESSORIES: HEADSETS
            // ==========================================
            [
                'id' => 2030,
                'name' => 'SteelSeries Arctis Nova 7 Wireless Multi-System Gaming Headset',
                'slot' => 'headsets',
                'category' => 'Peripherals',
                'subCategory' => 'Headsets',
                'brand' => 'SteelSeries',
                'wattage' => 2,
                'price' => 58000.00,
                'originalPrice' => 64000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Audio' => 'Nova Acoustic System High Fidelity Drivers',
                    'Wireless' => 'Simultaneous 2.4GHz & Bluetooth',
                    'Battery' => '38 Hours USB-C Fast Charge',
                ],
            ],

            // ==========================================
            // 16. ACCESSORIES: UPS & POWER
            // ==========================================
            [
                'id' => 2040,
                'name' => 'APC Back-UPS Pro BR 1500VA Battery Backup & Surge Protector',
                'slot' => 'ups',
                'category' => 'Products',
                'subCategory' => 'UPS',
                'brand' => 'APC',
                'wattage' => 0,
                'price' => 74500.00,
                'originalPrice' => 82000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Capacity' => '1500VA / 865 Watts',
                    'Outlets' => '10 Outlets (6 battery backup, 4 surge)',
                    'Regulation' => 'Automatic Voltage Regulation (AVR)',
                ],
            ],
            [
                'id' => 2041,
                'name' => 'Prolink PRO1201SFC 1200VA Line-Interactive Super-Fast Charging UPS',
                'slot' => 'ups',
                'category' => 'Products',
                'subCategory' => 'UPS',
                'brand' => 'Prolink',
                'wattage' => 0,
                'price' => 32500.00,
                'originalPrice' => 36000.00,
                'image' => '/images/products/sample-keyboard.jpg',
                'inStock' => true,
                'specs' => [
                    'Capacity' => '1200VA / 720W',
                    'Battery' => 'Dual 12V 8.2AH with Fast Charger',
                    'Protection' => 'Overload, Discharge, Overcharge Protection',
                ],
            ],
        ];
    }

    /**
     * Curated pre-built configurations for one-click build loading
     */
    protected function getPreBuiltTemplates(): array
    {
        return [
            [
                'id' => 'budget-esports',
                'name' => 'Budget Esports Challenger',
                'subtitle' => 'High FPS 1080p gaming for Valorant, CS2, Fortnite & Apex',
                'badge' => 'Value Pick',
                'badgeColor' => 'emerald',
                'targetChipset' => 'AMD',
                'totalWattage' => 380,
                'estimatedPrice' => 395000.00,
                'parts' => [
                    'processors' => 1002,     // Ryzen 5 7600X
                    'motherboards' => 1012,   // Gigabyte B650M
                    'memory' => 1020,         // Corsair 32GB DDR5
                    'graphic-cards' => 1034,  // RTX 4060 8GB
                    'ssd' => 1043,            // Crucial P3 Plus 1TB
                    'power-supply' => 1063,   // Corsair CV650
                    'coolers' => 1072,        // Thermalright Peerless Assassin
                    'pc-cases' => 1083,       // Montech AIR 903 MAX
                    'fans' => 1090,           // Arctic MX-6
                ],
            ],
            [
                'id' => 'mid-range-beast',
                'name' => '1440p Battle Station Beast',
                'subtitle' => 'Ultra settings 1440p ray-tracing with the king of gaming CPUs',
                'badge' => 'Most Popular',
                'badgeColor' => 'cyan',
                'targetChipset' => 'AMD',
                'totalWattage' => 580,
                'estimatedPrice' => 745000.00,
                'parts' => [
                    'processors' => 1001,     // Ryzen 7 7800X3D
                    'motherboards' => 1010,   // MSI MAG B650 Tomahawk
                    'memory' => 1020,         // Corsair 32GB 6000MHz
                    'graphic-cards' => 1032,  // RTX 4070 Ti Super 16GB
                    'ssd' => 1040,            // Samsung 990 PRO 2TB
                    'power-supply' => 1060,   // Corsair RM850x
                    'coolers' => 1070,        // NZXT Kraken Elite 360
                    'pc-cases' => 1080,       // Lian Li O11 Dynamic EVO
                    'fans' => 1091,           // Lian Li Uni Fan SL-Infinity
                ],
            ],
            [
                'id' => 'ultra-enthusiast-4k',
                'name' => 'Enthusiast 4K Titan Battlerig',
                'subtitle' => 'Uncompromising 4K max ray tracing with 24GB VRAM & flagship i9',
                'badge' => 'Top Performance',
                'badgeColor' => 'purple',
                'targetChipset' => 'Intel',
                'totalWattage' => 880,
                'estimatedPrice' => 1520000.00,
                'parts' => [
                    'processors' => 1004,     // Intel Core i9-14900K
                    'motherboards' => 1013,   // ASUS ROG MAXIMUS Z790 HERO
                    'memory' => 1022,         // Corsair Dominator 64GB
                    'graphic-cards' => 1030,  // RTX 4090 24GB
                    'ssd' => 1040,            // Samsung 990 PRO 2TB
                    'extra-ssd' => 1095,      // Kingston NV2 1TB
                    'power-supply' => 1061,   // ASUS ROG Thor 1000W
                    'coolers' => 1070,        // NZXT Kraken Elite 360
                    'pc-cases' => 1081,       // NZXT H9 Flow
                    'fans' => 1091,           // Lian Li Uni Fan SL-Infinity
                ],
            ],
            [
                'id' => 'content-creator-workstation',
                'name' => 'Pro Creator & AI Workstation',
                'subtitle' => 'Blender, Unreal Engine, Premiere Pro 8K & local LLM acceleration',
                'badge' => 'Studio Grade',
                'badgeColor' => 'amber',
                'targetChipset' => 'AMD',
                'totalWattage' => 650,
                'estimatedPrice' => 985000.00,
                'parts' => [
                    'processors' => 1003,     // Ryzen 9 7950X3D (16C/32T)
                    'motherboards' => 1011,   // ASUS ROG STRIX B650-A
                    'memory' => 1022,         // 64GB DDR5
                    'graphic-cards' => 1031,  // RTX 4080 Super 16GB
                    'ssd' => 1040,            // Samsung 990 PRO 2TB
                    'hard-disk' => 1051,      // WD Blue 4TB HDD
                    'power-supply' => 1060,   // Corsair RM850x
                    'coolers' => 1071,        // Corsair iCUE LINK H150i
                    'pc-cases' => 1080,       // Lian Li O11 Dynamic EVO
                    'fans' => 1090,           // Arctic MX-6
                ],
            ],
        ];
    }
}
