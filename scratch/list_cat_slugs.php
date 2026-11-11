<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cats = App\Models\Category::all(['id', 'name', 'slug', 'parent_id']);
foreach ($cats as $c) {
    $parent = $c->parent_id ? "parent_id: {$c->parent_id}" : "PARENT";
    echo "ID {$c->id}: {$c->name} | slug: '{$c->slug}' | {$parent}\n";
}

