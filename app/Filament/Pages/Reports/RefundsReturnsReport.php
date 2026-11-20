<?php

namespace App\Filament\Pages\Reports;

class RefundsReturnsReport extends BaseReportPage
{
    protected static ?string $navigationLabel = 'Refunds & Returns Report';

    protected static ?string $title = 'Refunds & Returns Report';

    protected static ?string $slug = 'reports/refunds-returns';

    protected static ?string $navigationIcon = 'heroicon-o-receipt-refund';

    protected static ?int $navigationSort = 8;

    public function getReportType(): string
    {
        return 'refunds_returns';
    }
}
