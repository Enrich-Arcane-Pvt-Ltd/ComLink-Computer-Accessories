<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p = App\Models\Product::find(317);
echo "ID 317:\n";
echo "Code: " . $p->product_code . "\n";
echo "Name: " . $p->name . "\n";
echo "Images: " . json_encode($p->images) . "\n";

