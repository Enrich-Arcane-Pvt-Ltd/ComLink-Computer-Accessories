<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$brands = App\Models\Brand::all();
foreach ($brands as $b) {
    echo "ID {$b->id}: {$b->name} (slug: {$b->slug})\n";
}

