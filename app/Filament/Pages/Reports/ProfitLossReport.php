<?php

namespace App\Filament\Pages\Reports;

class ProfitLossReport extends BaseReportPage
{
    protected static ?string $navigationLabel = 'Profit / Loss Report';

    protected static ?string $title = 'Profit / Loss Analysis Report';

    protected static ?string $slug = 'reports/profit-loss';

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?int $navigationSort = 7;

    public function getReportType(): string
    {
        return 'profit_loss';
    }
}
