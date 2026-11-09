<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cats = App\Models\Category::with('children')->whereNull('parent_id')->orderBy('sort_order')->get();
foreach ($cats as $c) {
    echo "Parent Category: {$c->name} (slug: {$c->slug}, id: {$c->id})\n";
    foreach ($c->children as $sub) {
        echo "  -> Subcategory: {$sub->name} (slug: {$sub->slug}, id: {$sub->id})\n";
    }
}

