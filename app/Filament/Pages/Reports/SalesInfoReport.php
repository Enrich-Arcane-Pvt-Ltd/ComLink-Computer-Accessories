<?php

namespace App\Filament\Pages\Reports;

class SalesInfoReport extends BaseReportPage
{
    protected static ?string $navigationLabel = 'Sales Info Report';

    protected static ?string $title = 'Sales Info Report';

    protected static ?string $slug = 'reports/sales-info';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 6;

    public function getReportType(): string
    {
        return 'sales_info';
    }
}
