<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$vCount = App\Models\ProductVariant::count();
echo "Total Product Variants in DB: {$vCount}\n";
if ($vCount > 0) {
    foreach (App\Models\ProductVariant::take(5)->get() as $v) {
        echo "- [Product ID {$v->product_id}] {$v->variant_name} | SKU: {$v->sku} | Price: {$v->sale_price}\n";
    }
}

