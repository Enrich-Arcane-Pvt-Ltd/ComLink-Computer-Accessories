<?php

namespace App\Filament\Pages\Reports;

class GrnAnalyzerReport extends BaseReportPage
{
    protected static ?string $navigationLabel = 'GRN Analyzer Report';

    protected static ?string $title = 'GRN Analyzer Report';

    protected static ?string $slug = 'reports/grn-analyzer';

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?int $navigationSort = 2;

    public function getReportType(): string
    {
        return 'grn_analyzer';
    }
}
