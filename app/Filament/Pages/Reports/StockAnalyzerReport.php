<?php

namespace App\Filament\Pages\Reports;

class StockAnalyzerReport extends BaseReportPage
{
    protected static ?string $navigationLabel = 'Stock Analyzer Report';

    protected static ?string $title = 'Stock Analyzer Report';

    protected static ?string $slug = 'reports/stock-analyzer';

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?int $navigationSort = 4;

    public function getReportType(): string
    {
        return 'stock_analyzer';
    }
}
