<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Filament\Resources\InventoryProductResource\Pages\ListInventoryProducts;
use App\Filament\Resources\InventoryProductResource\Tables\InventoryProductTable;
use Filament\Tables\Table;

$livewire = new ListInventoryProducts();
$table = InventoryProductTable::table(Table::make($livewire));

echo "Columns: " . json_encode($table->getFiltersFormColumns()) . "\n";
$filter = $table->getFilter('product_filter');
echo "Filter column span: " . json_encode($filter->getColumnSpan()) . "\n";

