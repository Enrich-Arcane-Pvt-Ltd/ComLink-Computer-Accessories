<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$prods = App\Models\Product::with(['categories', 'brand', 'inventoryProduct'])->where('id', '>=', 537)->orderBy('id')->get();
echo "Found " . $prods->count() . " products from ID 537 onwards:\n";

foreach ($prods as $p) {
    $catNames = $p->categories->pluck('name')->join(' > ');
    $brandName = $p->brand ? $p->brand->name : 'No Brand';
    $imgCount = is_array($p->images) ? count($p->images) : 0;
    $img1 = is_array($p->images) && count($p->images) > 0 ? $p->images[0] : 'None';
    $imgExists = file_exists(storage_path('app/public/' . $img1)) ? 'EXISTS' : 'MISSING';
    $inv = $p->inventoryProduct ? "Inv ID: {$p->inventoryProduct->id}" : "NO INV";
    $specsCount = count($p->technical_specifications ?? []);
    
    echo "ID {$p->id} [{$p->product_code}]: {$p->name} | Brand: {$brandName} | [{$catNames}] | Price: {$p->sale_price} | Img: {$img1} ({$imgExists}) | {$inv} | Specs: {$specsCount}\n";
}

