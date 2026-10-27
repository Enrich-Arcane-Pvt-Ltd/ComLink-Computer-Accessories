<?php

namespace App\Filament\Resources\SalesInfoResource\Pages;

use App\Filament\Resources\SalesInfoResource\SalesInfoResource;
use App\Filament\Resources\SalesInfoResource\Widgets\SalesInfoOverviewWidget;
use Filament\Resources\Pages\ListRecords;

class ListSalesInfos extends ListRecords
{
    protected static string $resource = SalesInfoResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SalesInfoOverviewWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }
}
