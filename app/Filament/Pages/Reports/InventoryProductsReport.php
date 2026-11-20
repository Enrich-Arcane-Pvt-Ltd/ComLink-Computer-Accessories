<?php

namespace App\Filament\Pages\Reports;

class InventoryProductsReport extends BaseReportPage
{
    protected static ?string $navigationLabel = 'Inventory Products Report';

    protected static ?string $title = 'Inventory Products Report';

    protected static ?string $slug = 'reports/inventory-products';

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?int $navigationSort = 1;

    public function getReportType(): string
    {
        return 'inventory_products';
    }
}
