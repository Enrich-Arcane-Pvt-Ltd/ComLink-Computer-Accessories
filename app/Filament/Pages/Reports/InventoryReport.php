<?php

namespace App\Filament\Pages\Reports;

class InventoryReport extends BaseReportPage
{
    protected static ?string $navigationLabel = 'Inventory Report';

    protected static ?string $title = 'Inventory Report';

    protected static ?string $slug = 'reports/inventory';

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?int $navigationSort = 3;

    public function getReportType(): string
    {
        return 'inventory';
    }
}
