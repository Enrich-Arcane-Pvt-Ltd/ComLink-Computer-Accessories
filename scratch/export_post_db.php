<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$prods = App\Models\Product::where('id', '>=', 537)->get(['id', 'product_code', 'name', 'images', 'price', 'sale_price']);
file_put_contents('scratch/post_db_prods.json', $prods->toJson());
echo "Exported " . $prods->count() . " products to scratch/post_db_prods.json\n";

