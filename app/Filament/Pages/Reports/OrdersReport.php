<?php

namespace App\Filament\Pages\Reports;

class OrdersReport extends BaseReportPage
{
    protected static ?string $navigationLabel = 'Orders Report';

    protected static ?string $title = 'Orders Report';

    protected static ?string $slug = 'reports/orders';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 5;

    public function getReportType(): string
    {
        return 'orders';
    }
}
