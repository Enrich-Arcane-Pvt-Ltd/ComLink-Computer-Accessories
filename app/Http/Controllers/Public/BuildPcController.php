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
        $templates = $this->getPreBuiltTemplates($products);

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
     * Get real products from database structured for the PC builder slots
     */
    protected function getBuilderProducts(): array
    {
        $dbProducts = Product::where('is_visible', true)
            ->with(['brand', 'categories', 'variants'])
            ->get();

        $builderProducts = [];

        foreach ($dbProducts as $product) {
            $slots = $this->getSlotsForProduct($product);
            foreach ($slots as $slot) {
                $builderProducts[] = $this->formatBuilderProduct($product, $slot);
            }
        }

        return $builderProducts;
    }

    /**
     * Map a product to its applicable PC Builder slot(s)
     */
    protected function getSlotsForProduct(Product $product): array
    {
        $slots = [];

        $slugs = $product->categories->pluck('slug')->map(fn ($s) => strtolower(trim((string) $s)))->all();
        $names = $product->categories->pluck('name')->map(fn ($n) => strtolower(trim((string) $n)))->all();

        // Exclude products explicitly belonging to non-PC builder categories (like Laptops, Software, Consoles, etc.)
        $excludedSlugs = ['laptops', 'gaming-laptops', 'software', 'consoles', 'controllers', 'chairs', 'tables', 'printers', 'scanners', 'toners-cartridges'];
        if (! empty(array_intersect($excludedSlugs, $slugs))) {
            return [];
        }

        $hasSlug = fn (...$check) => ! empty(array_intersect($check, $slugs));
        $hasName = function (...$keywords) use ($names) {
            foreach ($names as $name) {
                foreach ($keywords as $kw) {
                    if (str_contains($name, $kw)) {
                        return true;
                    }
                }
            }
            return false;
        };

        // 1. Processors
        if ($hasSlug('processors', 'processor', 'cpu') || $hasName('processor', 'cpu')) {
            $slots[] = 'processors';
        }

        // 2. Motherboards
        if ($hasSlug('motherboards', 'motherboard', 'mainboard', 'mobo') || $hasName('motherboard', 'mainboard')) {
            $slots[] = 'motherboards';
        }

        // 3. Memory
        if ($hasSlug('memory', 'ram', 'memory-cards', 'ddr4', 'ddr5') || $hasName('memory', 'ram')) {
            $slots[] = 'memory';
        }

        // 4. Graphics Cards
        if ($hasSlug('graphic-cards', 'graphics-cards', 'graphic-card', 'gpu', 'vga') || $hasName('graphic', 'gpu', 'vga')) {
            $slots[] = 'graphic-cards';
        }

        // 5. SSD (Primary and Extra)
        if ($hasSlug('ssd', 'nvme', 'm2-ssd', 'solid-state-drive') || $hasName('ssd', 'nvme')) {
            $slots[] = 'ssd';
            $slots[] = 'extra-ssd';
        }

        // 6. Hard Disk
        if ($hasSlug('hard-disk', 'hard-disks', 'hdd', 'hard-drive') || $hasName('hard disk', 'hdd', 'hard drive')) {
            $slots[] = 'hard-disk';
        }

        // 7. Power Supply
        if ($hasSlug('power-supply', 'psu', 'smps') || $hasName('power supply', 'psu')) {
            $slots[] = 'power-supply';
        }

        // 8. Coolers
        if ($hasSlug('coolers', 'cooler', 'cpu-cooler', 'liquid-cooler', 'aio-cooler') || $hasName('cooler')) {
            $slots[] = 'coolers';
        }

        // 9. PC Cases
        if ($hasSlug('pc-cases', 'cases', 'chassis', 'casing') || $hasName('case', 'chassis', 'casing')) {
            $slots[] = 'pc-cases';
        }

        // 10. Fans / Thermal Paste
        if ($hasSlug('thermal-paste', 'fans', 'cooling-fans', 'case-fans') || $hasName('thermal', 'paste', 'fan')) {
            $slots[] = 'fans';
        }

        // 11. Monitors & Displays
        if ($hasSlug('monitors-displays', 'monitors', 'monitor', 'displays', 'display') || $hasName('monitor', 'display')) {
            $slots[] = 'monitors-displays';
        }

        // 12. Keyboards
        if ($hasSlug('keyboards', 'keyboard') || $hasName('keyboard')) {
            $slots[] = 'keyboards';
        }

        // 13. Mouse
        if ($hasSlug('mouse', 'mice', 'gaming-mouse') || $hasName('mouse')) {
            $slots[] = 'mouse';
        }

        // 14. Headsets
        if ($hasSlug('headsets', 'headset', 'headphones') || $hasName('headset', 'headphone')) {
            $slots[] = 'headsets';
        }

        // 15. UPS
        if ($hasSlug('ups', 'power-backup') || $hasName('ups')) {
            $slots[] = 'ups';
        }

        // Fallback: If product wasn't categorized into slots, check product title
        if (empty($slots)) {
            $title = strtolower($product->name);
            if (str_contains($title, 'laptop') || str_contains($title, 'notebook')) {
                return [];
            }
            if (str_contains($title, 'processor') || str_contains($title, 'ryzen') || preg_match('/\b(i3|i5|i7|i9)\b/i', $title)) {
                $slots[] = 'processors';
            } elseif (str_contains($title, 'motherboard') || preg_match('/\b(b650|b760|z790|x670|a620|b550|b450)\b/i', $title)) {
                $slots[] = 'motherboards';
            } elseif (str_contains($title, 'ddr5') || str_contains($title, 'ddr4') || str_contains($title, 'ram')) {
                $slots[] = 'memory';
            } elseif (str_contains($title, 'rtx ') || str_contains($title, 'geforce') || str_contains($title, 'radeon rx') || str_contains($title, 'graphics card')) {
                $slots[] = 'graphic-cards';
            } elseif (str_contains($title, 'ssd') || str_contains($title, 'nvme')) {
                $slots[] = 'ssd';
                $slots[] = 'extra-ssd';
            } elseif (str_contains($title, 'hard drive') || str_contains($title, 'barracuda') || str_contains($title, 'wd blue') || str_contains($title, 'hdd')) {
                $slots[] = 'hard-disk';
            } elseif (str_contains($title, 'power supply') || str_contains($title, 'psu') || str_contains($title, '80 plus')) {
                $slots[] = 'power-supply';
            } elseif (str_contains($title, 'cooler') || str_contains($title, 'kraken') || str_contains($title, 'liquid cool')) {
                $slots[] = 'coolers';
            } elseif (str_contains($title, 'chassis') || str_contains($title, 'pc case') || str_contains($title, 'casing')) {
                $slots[] = 'pc-cases';
            } elseif (str_contains($title, 'thermal paste') || str_contains($title, 'case fan')) {
                $slots[] = 'fans';
            } elseif (str_contains($title, 'monitor') || str_contains($title, 'display')) {
                $slots[] = 'monitors-displays';
            } elseif (str_contains($title, 'keyboard')) {
                $slots[] = 'keyboards';
            } elseif (str_contains($title, 'mouse') && ! str_contains($title, 'pad')) {
                $slots[] = 'mouse';
            } elseif (str_contains($title, 'headset') || str_contains($title, 'headphone')) {
                $slots[] = 'headsets';
            } elseif (str_contains($title, 'ups')) {
                $slots[] = 'ups';
            }
        }

        return array_values(array_unique($slots));
    }

    /**
     * Format a real Product model for the PC builder slot
     */
    protected function formatBuilderProduct(Product $product, string $slot): array
    {
        $mainCat = $product->categories->firstWhere('parent_id', null) ?? $product->categories->first();
        $subCat = $product->categories->firstWhere('parent_id', '!=', null) ?? ($product->categories->count() > 1 ? $product->categories->last() : null);

        $catName = $mainCat?->name ?? 'PC Components';
        $subCatName = $subCat?->name ?? $catName;
        $brand = $product->brand?->name ?? 'Generic';

        $origPrice = (float) $product->price;
        $price = ($product->sale_price !== null && (float) $product->sale_price > 0)
            ? (float) $product->sale_price
            : $origPrice;

        $stockCount = (int) ($product->show_quantity ?? $product->quantity ?? 0);
        $inStock = $stockCount > 0;

        // Image resolution: check color variants first, then product images
        $image = '/images/placeholder.png';
        if ($product->hasColorVariants() && $product->variants->isNotEmpty()) {
            $firstVariant = $product->variants->first(fn ($v) => ! empty($v->image));
            if ($firstVariant && ! empty($firstVariant->image)) {
                $raw = $firstVariant->image;
                $image = (str_starts_with($raw, 'http') || str_starts_with($raw, '/'))
                    ? $raw
                    : asset('storage/' . $raw);
            }
        }

        if ($image === '/images/placeholder.png' && ! empty($product->images) && is_array($product->images)) {
            $firstImg = $product->images[0] ?? null;
            if (! empty($firstImg)) {
                $image = (str_starts_with($firstImg, 'http') || str_starts_with($firstImg, '/'))
                    ? $firstImg
                    : asset('storage/' . $firstImg);
            }
        }

        $tech = is_array($product->technical_specifications) ? $product->technical_specifications : [];
        $main = is_array($product->main_specifications) ? $product->main_specifications : [];

        // Helper to find spec case-insensitively with loose key matching
        $getSpec = function (array $source, ...$keys) {
            foreach ($keys as $k) {
                if (isset($source[$k]) && ! is_null($source[$k]) && trim((string) $source[$k]) !== '') {
                    return trim((string) $source[$k]);
                }
                $cleanK = strtolower(str_replace([' ', '.', '-', '_'], '', $k));
                foreach ($source as $sk => $sv) {
                    $cleanSK = strtolower(str_replace([' ', '.', '-', '_'], '', (string) $sk));
                    if ($cleanSK === $cleanK && ! is_null($sv) && trim((string) $sv) !== '') {
                        return trim((string) $sv);
                    }
                }
            }
            return null;
        };

        // 1. Platform (AMD vs Intel)
        $rawPlatform = $getSpec($main, 'mb_platform', 'platform')
            ?? $getSpec($tech, 'platform', 'processor_type', 'architecture')
            ?? null;
        $platform = null;
        if (in_array($slot, ['processors', 'motherboards'])) {
            $textToScan = strtolower($product->name . ' ' . ($product->brand?->name ?? '') . ' ' . ($rawPlatform ?? ''));

            if (str_contains($textToScan, 'amd') || str_contains($textToScan, 'ryzen') || str_contains($textToScan, 'am5') || str_contains($textToScan, 'am4') || str_contains($textToScan, 'b650') || str_contains($textToScan, 'x670') || str_contains($textToScan, 'b550') || str_contains($textToScan, 'a620')) {
                $platform = 'AMD';
            } elseif (str_contains($textToScan, 'intel') || str_contains($textToScan, 'core i') || str_contains($textToScan, 'lga') || str_contains($textToScan, 'z790') || str_contains($textToScan, 'b760') || str_contains($textToScan, 'h610') || str_contains($textToScan, 'z690')) {
                $platform = 'Intel';
            }
        }

        // 2. CPU Socket (AM4, AM5, LGA1700, LGA1200, etc.)
        $rawSocket = $getSpec($main, 'cpu_socket', 'cpu_soket', 'socket', 'socket_type')
            ?? $getSpec($tech, 'socket', 'cpu_socket', 'socket_type')
            ?? null;
        $socket = null;
        if (in_array($slot, ['processors', 'motherboards', 'coolers'])) {
            $textToScan = ($rawSocket ?? '') . ' ' . $product->name;

            if (preg_match('/\b(AM5|AM4|AM3\+|LGA\s*1700|LGA\s*1200|LGA\s*1151|LGA\s*1155|sTR5|TR4)\b/i', $textToScan, $m)) {
                $socket = strtoupper(str_replace(' ', '', $m[1]));
            } else {
                if (preg_match('/\b(Ryzen\s+[789]\d{3}|B650|X670|A620)\b/i', $textToScan)) {
                    $socket = 'AM5';
                } elseif (preg_match('/\b(Ryzen\s+[12345]\d{3}|B550|B450|X570|A520)\b/i', $textToScan)) {
                    $socket = 'AM4';
                } elseif (preg_match('/\b(1[234]\d{2}[0-9A-Z]*|Z790|B760|H610|Z690|B660)\b/i', $textToScan)) {
                    $socket = 'LGA1700';
                } elseif (preg_match('/\b(1[01]\d{2}[0-9A-Z]*|Z490|B560|H510)\b/i', $textToScan)) {
                    $socket = 'LGA1200';
                }
            }
        }

        // 3. Chipset Model (B650, B550, Z790, B760, etc.)
        $chipsetModel = $getSpec($main, 'chipset_model', 'chipset')
            ?? $getSpec($tech, 'chipset')
            ?? null;
        if (! $chipsetModel && $slot === 'motherboards') {
            if (preg_match('/\b(X670E|X670|B650E|B650|A620|X570|B550|A520|B450|Z790|Z690|B760|B660|H610)\b/i', $product->name, $m)) {
                $chipsetModel = strtoupper($m[1]);
            }
        }

        // 4. Memory Generation (DDR4 vs DDR5)
        $rawMemGen = $getSpec($main, 'memory_gen', 'generation', 'memory_generation')
            ?? $getSpec($tech, 'memory_generation', 'architecture')
            ?? null;
        $memoryGen = null;
        if ($rawMemGen) {
            $upper = strtoupper($rawMemGen);
            if (str_contains($upper, 'DDR5')) {
                $memoryGen = 'DDR5';
            } elseif (str_contains($upper, 'DDR4')) {
                $memoryGen = 'DDR4';
            }
        }
        if (! $memoryGen && preg_match('/\b(DDR5|DDR4|DDR3)\b/i', $product->name . ' ' . json_encode($main) . ' ' . json_encode($tech), $m)) {
            $memoryGen = strtoupper($m[1]);
        }

        // 5. Wattage / Power Consumption / Power Output
        $parseWatts = function ($val) {
            if (is_numeric($val) && (int) $val > 0) {
                return (int) $val;
            }
            if (is_string($val) && preg_match('/(\d{2,4})\s*W/i', $val, $m)) {
                return (int) $m[1];
            }
            return null;
        };

        // For Power Supplies: look for Power Output first
        $rawPsuOutput = $getSpec($main, 'power_output', 'output_wattage');
        $rawPower = $parseWatts($rawPsuOutput)
            ?? $parseWatts($getSpec($tech, 'power_consumption', 'tdp', 'power'))
            ?? $parseWatts($getSpec($main, 'power_consumption', 'wattage'));

        $wattage = $rawPower;
        if (! $wattage && preg_match('/(\d{3,4})\s*W\b/i', $product->name, $m)) {
            $wattage = (int) $m[1];
        }

        if (! $wattage || $wattage <= 0) {
            $wattage = match ($slot) {
                'power-supply' => 650,
                'processors' => 105,
                'graphic-cards' => 220,
                'motherboards' => 45,
                'memory' => 15,
                'coolers' => 25,
                'ssd', 'extra-ssd' => 8,
                'hard-disk' => 10,
                'fans' => 10,
                default => 0,
            };
        }

        // 6. GPU Recommended Minimum PSU Requirement
        $rawMinPsu = $getSpec($main, 'power_supply_requirement', 'psu_requirement', 'recommended_psu')
            ?? $getSpec($tech, 'power_supply_requirement');
        $minPsuWatts = $parseWatts($rawMinPsu);

        // 7. Power Supply Rating (80 Plus Bronze/Gold/etc.)
        $psuRating = $getSpec($main, 'power_rating', 'modular');

        // Format specs key-values
        $specs = [];
        if (! empty($tech)) {
            foreach ($tech as $key => $val) {
                if (! is_null($val) && trim((string) $val) !== '' && ! in_array($key, ['category_segment', 'model_series'])) {
                    $label = ucwords(str_replace('_', ' ', $key));
                    $specs[$label] = (string) $val;
                    if (count($specs) >= 4) {
                        break;
                    }
                }
            }
        }
        if (empty($specs) && ! empty($main)) {
            foreach ($main as $key => $val) {
                if (! is_null($val) && trim((string) $val) !== '') {
                    $label = ucwords(str_replace('_', ' ', $key));
                    $specs[$label] = (string) $val;
                    if (count($specs) >= 4) {
                        break;
                    }
                }
            }
        }
        if (empty($specs)) {
            $specs['Condition'] = $product->condition ?? 'Brand New';
            $specs['Brand'] = $brand;
            if ($product->warranty_duration) {
                $specs['Warranty'] = $product->warranty_duration . ' ' . ($product->warranty_unit ?? 'Months');
            }
            if ($product->sku) {
                $specs['SKU'] = $product->sku;
            }
        }

        return [
            'id' => (int) $product->id,
            'name' => $product->name,
            'slot' => $slot,
            'category' => $catName,
            'subCategory' => $subCatName,
            'brand' => $brand,
            'platform' => $platform,
            'chipset' => $chipsetModel ?? $platform,
            'socket' => $socket,
            'memoryGen' => $memoryGen,
            'wattage' => $wattage,
            'minPsuWatts' => $minPsuWatts,
            'psuRating' => $psuRating,
            'price' => $price,
            'originalPrice' => $origPrice,
            'image' => $image,
            'inStock' => $inStock,
            'specs' => $specs,
        ];
    }

    /**
     * Generate pre-built templates dynamically from real catalog products
     */
    protected function getPreBuiltTemplates(array $builderProducts): array
    {
        $bySlot = [];
        foreach ($builderProducts as $bp) {
            $bySlot[$bp['slot']][] = $bp;
        }

        $pick = function (string $slot, ?string $chipset = null) use ($bySlot) {
            $candidates = $bySlot[$slot] ?? [];
            if ($chipset && in_array($slot, ['processors', 'motherboards'])) {
                $filtered = array_values(array_filter($candidates, fn ($p) => ($p['chipset'] ?? null) === $chipset));
                if (! empty($filtered)) {
                    return $filtered[0];
                }
            }
            return $candidates[0] ?? null;
        };

        $templates = [];

        $amdCpu = $pick('processors', 'AMD');
        $intelCpu = $pick('processors', 'Intel');
        $anyCpu = $amdCpu ?? $intelCpu ?? $pick('processors');

        if ($anyCpu) {
            $chipset = $anyCpu['chipset'] ?? 'AMD';
            $mobo = $pick('motherboards', $chipset) ?? $pick('motherboards');
            $ram = $pick('memory');
            $gpu = $pick('graphic-cards');
            $ssd = $pick('ssd');
            $psu = $pick('power-supply');
            $case = $pick('pc-cases');
            $cooler = $pick('coolers');
            $fans = $pick('fans');

            $parts = array_filter([
                'processors' => $anyCpu['id'] ?? null,
                'motherboards' => $mobo['id'] ?? null,
                'memory' => $ram['id'] ?? null,
                'graphic-cards' => $gpu['id'] ?? null,
                'ssd' => $ssd['id'] ?? null,
                'power-supply' => $psu['id'] ?? null,
                'coolers' => $cooler['id'] ?? null,
                'pc-cases' => $case['id'] ?? null,
                'fans' => $fans['id'] ?? null,
            ]);

            if (! empty($parts)) {
                $totalWattage = 0;
                $estPrice = 0;
                foreach ($parts as $slot => $id) {
                    $found = collect($builderProducts)->firstWhere('id', $id);
                    if ($found) {
                        $totalWattage += $found['wattage'] ?? 0;
                        $estPrice += $found['price'] ?? 0;
                    }
                }

                $templates[] = [
                    'id' => 'custom-balanced-build',
                    'name' => 'ComLink Verified Rig',
                    'subtitle' => 'Pre-configured build with genuine tested components from store inventory',
                    'badge' => 'In Stock',
                    'badgeColor' => 'emerald',
                    'targetChipset' => $chipset,
                    'totalWattage' => max($totalWattage, 450),
                    'estimatedPrice' => $estPrice,
                    'parts' => $parts,
                ];
            }
        }

        return $templates;
    }
}
