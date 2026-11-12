<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ProductSpecification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSpecificationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding tailored Product Specifications for all Categories and Subcategories...');

        // 1. Truncate / clean up existing product specifications
        ProductSpecification::truncate();

        // 2. Standard Technical Specifications Definition
        $standardTechSpecs = [
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

        // 3. Tailored Main Specifications Map: [Category Slug => [Subcategory Slug => [Attributes]]]
        $specMap = [
            'laptops' => [
                '_direct' => ['Processor', 'Memory (RAM)', 'Internal Storage', 'Display', 'Graphics', 'Operating System', 'Connectivity', 'Battery'],
            ],
            'pc-components' => [
                'ram' => ['Memory Capacity', 'Memory Type', 'Module Type', 'Frequency Speed', 'Voltage'],
                'processors' => ['Processor Model', 'Socket Type', 'Cores / Threads', 'Clock Speed', 'Cache Memory'],
                'processor-fans' => ['Compatible Sockets', 'Cooler Type', 'Connector Type', 'Max Fan Speed', 'Heatsink Material'],
                'coolers' => ['Cooler Type', 'Compatible Sockets', 'Fan Speed Control', 'Heatsink Material', 'Noise Level'],
                'motherboards' => ['CPU Socket', 'Chipset', 'Form Factor', 'Memory Support', 'Storage Interface', 'Expansion Slots', 'Video Outputs', 'Storage Ports'],
                'memory-cards' => ['Storage Capacity', 'Speed Class', 'Max Read Speed', 'Max Write Speed', 'Video Recording Support', 'Durability', 'Form Factor', 'Compatibility'],
                'hard-disk' => ['Storage Capacity', 'Form Factor', 'Interface', 'Rotational Speed', 'Buffer / Cache'],
                'ssd' => ['Storage Capacity', 'Interface', 'Form Factor', 'Max Read Speed', 'Max Write Speed', 'NAND Flash Type'],
                'graphic-cards' => ['GPU Engine', 'Video Memory (VRAM)', 'Memory Bus', 'Interface', 'Display Outputs'],
                'thermal-paste' => ['Thermal Conductivity', 'Weight / Volume', 'Viscosity', 'Operating Temperature'],
                'power-supply' => ['Rated Wattage', 'Form Factor', 'PCIe Connector', 'Cooling Fan', 'Main Connector', 'Efficiency Rating'],
                'pc-cases' => ['Case Type / Form Factor', 'Motherboard Support', 'Drive Bays', 'Cooling & Fan Support', 'Front I/O Ports'],
            ],
            'peripherals' => [
                'headsets' => ['Audio Output', 'Connectivity', 'Microphone', 'Driver Diameter', 'Frequency Response', 'Lighting / Backlight'],
                'cooling-pads' => ['Compatible Laptop Size', 'Fan Configuration', 'Fan Speed Control', 'Lighting', 'USB Ports', 'Height Adjustment'],
                'keyboards' => ['Keyboard Type', 'Switch Type', 'Layout & Keys', 'Connectivity', 'Backlight / RGB', 'Anti-Ghosting'],
                'gaming-kits' => ['Kit Includes', 'Headset Audio', 'Keyboard Keys & Type', 'Mouse DPI', 'Connectivity', 'Lighting'],
                'mouse' => ['Resolution (DPI)', 'Connectivity', 'Sensor Type', 'Number of Buttons', 'Lighting / Backlight', 'Switch Durability'],
                'combo-packs' => ['Package Includes', 'Keyboard Type', 'Mouse DPI', 'Connectivity', 'Silent Clicking', 'Wireless Range'],
                'mouse-pad' => ['Dimensions & Size', 'Surface Material', 'Base Material', 'Edge Treatment', 'Lighting'],
                'speakers' => ['Speaker Configuration', 'Total Output Power', 'Connectivity', 'Frequency Response', 'Lighting / Backlight'],
                'webcam' => ['Video Resolution', 'Field of View', 'Microphone', 'Focus Type', 'Connectivity', 'Privacy Features'],
                'cables' => ['Cable Type', 'Length', 'Connector A', 'Connector B', 'Maximum Rating', 'Shielding'],
                'video-creator-equipments' => ['Equipment Type', 'Interface / Connection', 'Resolution Support', 'Lighting / Color Modes', 'Mounting / Stand'],
                'converters' => ['Converter Type', 'Input Connector', 'Output Connector', 'Resolution Support', 'Casing Material'],
                'adapters' => ['Adapter Type', 'Connector A', 'Connector B', 'Power Rating', 'Compatibility'],
            ],
            'monitors-displays' => [
                'displays' => ['Screen Size', 'Panel Technology', 'Resolution', 'Connector Type', 'Refresh Rate', 'Surface Finish'],
                'monitors' => ['Screen Size', 'Resolution', 'Refresh Rate', 'Response Time', 'Aspect Ratio', 'Panel Type', 'Inputs'],
            ],
            'gaming' => [
                'gaming-laptops' => ['Processor', 'Memory (RAM)', 'Internal Storage', 'Display', 'Graphics Card', 'Operating System', 'Battery'],
                'consoles' => ['Storage Capacity', 'Video Output', 'Audio Output', 'Connectivity', 'Included Controllers'],
                'controllers' => ['Connectivity', 'Platform Compatibility', 'Vibration Feedback', 'Battery Life', 'Lighting / Backlight'],
            ],
            'products' => [
                'system-units' => ['Processor', 'Memory (RAM)', 'Storage', 'Optical Drive', 'Form Factor'],
                'flash-drives' => ['Storage Capacity', 'Interface', 'Form Factor', 'Max Read Speed', 'Casing Material', 'Compatibility'],
                'ups' => ['Power Capacity', 'Nominal Input Voltage', 'Output Voltage', 'Battery Specification', 'Typical Backup Time', 'Output Sockets'],
                'power-banks' => ['Battery Capacity', 'Input Ports', 'Output Ports', 'Fast Charging Support', 'Dimensions & Weight'],
                'chairs' => ['Chair Type', 'Upholstery Material', 'Reclining Mechanism', 'Armrest Type', 'Footrest', 'Gas Lift Cylinder'],
                'tables' => ['Table Type', 'Dimensions', 'Frame Material', 'Surface Material', 'Height Adjustment'],
                'monitors-products' => ['Screen Size', 'Resolution', 'Refresh Rate', 'Response Time', 'Aspect Ratio', 'Panel Type', 'Inputs'],
                'range-extenders' => ['Device Type', 'Wireless Speed', 'Frequency Bands', 'Antenna Type', 'Ethernet Ports', 'Security'],
                'power-supplies' => ['Wattage', 'Form Factor', 'Input Voltage', 'Output Voltage', 'Output Current', 'Connectors'],
                'printers' => ['Print Technology', 'Print Speed', 'Print Resolution', 'Connectivity', 'Paper Input Capacity', 'Functions', 'Monthly Duty Cycle'],
                'toners-cartridges' => ['Consumable Type', 'Color', 'Page Yield', 'Compatible Printers'],
                'scanners' => ['Scanner Type', 'Scan Resolution', 'Scan Speed', 'Daily Duty Cycle', 'Connectivity'],
                'routers-adapters' => ['Device Type', 'Wireless Speed', 'Ethernet Ports', 'SIM Slot', 'Antennas', 'Security'],
                'ssd-storage' => ['Storage Capacity', 'Interface', 'Form Factor', 'Max Read Speed', 'Max Write Speed', 'NAND Flash Type', 'Endurance'],
                'ink-ribbons' => ['Consumable Type', 'Ink Volume', 'Page Yield', 'Compatible Models', 'Ribbon Length', 'Fabric Material'],
                'batteries' => ['Battery Type', 'Nominal Voltage', 'Package Quantity', 'Typical Capacity', 'Chemical System', 'Applications'],
                'pos-printers-accessories' => ['Device Type', 'Print Speed', 'Print Resolution', 'Interface Ports', 'Cutter Type', 'Paper Width'],
                'bluetooth-speakers' => ['Speaker Type / Channel', 'Output Power (RMS)', 'Bluetooth Version & Range', 'Battery Capacity & Playtime', 'Audio Inputs & Connectivity', 'Lighting / RGB Effects'],
                'switches' => ['Device Type', 'Port Configuration', 'Switching Capacity', 'Forwarding Rate', 'Casing Design', 'Power Design'],
                'wall-mounts' => ['Mount Type', 'Screen Size Compatibility', 'VESA Compatibility', 'Maximum Load Capacity', 'Material', 'Tilt & Swivel Range'],
                'laptop-bags-backpacks' => ['Laptop Compatibility', 'Material', 'Compartments', 'Color', 'Weight', 'Carrying Style'],
                'access-points' => ['Device Type', 'Wireless Standard', 'Wireless Speed', 'PoE Support', 'Antennas', 'Security Features'],
                'laptops-notebooks' => ['Processor', 'Memory (RAM)', 'Internal Storage', 'Display', 'Graphics', 'Operating System', 'Battery'],
                'rack-network-accessories' => ['Device Type', 'Port Count', 'Cabling Standard', 'Material Construction', 'Mounting Type'],
                'earbuds' => ['Headset / Earbud Type', 'Bluetooth Version & Range', 'Driver Size & Audio Quality', 'Battery Life & Charging Case', 'Microphone & Calls'],
                'laptop-stands' => ['Stand Type', 'Device Compatibility', 'Height Adjustment', 'Material', 'Weight Capacity'],
            ],
            'cameras' => [
                '_default' => ['Resolution', 'Night Vision', 'Connectivity', 'Audio', 'Storage Support', 'Field of View'],
            ],
            'networking' => [
                'network-cabinets' => ['Cabinet Size (U)', 'Dimensions', 'Mounting Type', 'Material', 'Door Type', 'Ventilation / Fan Support'],
            ],
        ];

        $mainCategories = Category::whereNull('parent_id')->with('children')->get();

        $totalMainCount = 0;
        $totalTechCount = 0;

        foreach ($mainCategories as $mainCat) {
            $catSlug = $mainCat->slug;

            // 1. Direct products for main category (if no subcategories, e.g. Laptops)
            if ($mainCat->children->isEmpty()) {
                $mainTitles = $specMap[$catSlug]['_direct'] ?? [
                    'Processor', 'Memory (RAM)', 'Internal Storage', 'Display', 'Graphics', 'Operating System', 'Connectivity', 'Battery'
                ];

                $sort = 1;
                foreach ($mainTitles as $title) {
                    ProductSpecification::create([
                        'category_id' => $mainCat->id,
                        'sub_category_id' => null,
                        'specification_type' => 'main',
                        'sub_section' => null,
                        'title' => $title,
                        'sort_order' => $sort++,
                    ]);
                    $totalMainCount++;
                }

                $techSort = 1;
                foreach ($standardTechSpecs as $subSec => $titles) {
                    foreach ($titles as $title) {
                        ProductSpecification::create([
                            'category_id' => $mainCat->id,
                            'sub_category_id' => null,
                            'specification_type' => 'technical',
                            'sub_section' => $subSec,
                            'title' => $title,
                            'sort_order' => $techSort++,
                        ]);
                        $totalTechCount++;
                    }
                }
                continue;
            }

            // 2. Subcategories
            foreach ($mainCat->children as $subCat) {
                $subSlug = $subCat->slug;

                // Determine tailored main spec titles
                $mainTitles = $specMap[$catSlug][$subSlug]
                    ?? $specMap[$catSlug]['_default']
                    ?? null;

                if (! $mainTitles) {
                    // Fallback based on category/sub name
                    if ($catSlug === 'cameras' || str_contains($subSlug, 'camera')) {
                        $mainTitles = ['Resolution', 'Night Vision', 'Connectivity', 'Audio', 'Storage Support', 'Field of View'];
                    } else {
                        $mainTitles = ['Storage Capacity', 'Interface', 'Connectivity', 'Material', 'Compatibility'];
                    }
                }

                $sort = 1;
                foreach ($mainTitles as $title) {
                    ProductSpecification::create([
                        'category_id' => $mainCat->id,
                        'sub_category_id' => $subCat->id,
                        'specification_type' => 'main',
                        'sub_section' => null,
                        'title' => $title,
                        'sort_order' => $sort++,
                    ]);
                    $totalMainCount++;
                }

                $techSort = 1;
                foreach ($standardTechSpecs as $subSec => $titles) {
                    foreach ($titles as $title) {
                        ProductSpecification::create([
                            'category_id' => $mainCat->id,
                            'sub_category_id' => $subCat->id,
                            'specification_type' => 'technical',
                            'sub_section' => $subSec,
                            'title' => $title,
                            'sort_order' => $techSort++,
                        ]);
                        $totalTechCount++;
                    }
                }
            }
        }

        $this->command->info("Seeded {$totalMainCount} tailored Main Specifications and {$totalTechCount} Technical Specifications across all categories!");
    }
}

