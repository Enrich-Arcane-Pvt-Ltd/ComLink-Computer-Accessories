<?php

namespace App\Filament\Resources\GrnResource\Pages;

use App\Filament\Resources\GrnResource\GrnResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\MaxWidth;

class ViewGrn extends ViewRecord
{
    protected static string $resource = GrnResource::class;

    public function getMaxContentWidth(): MaxWidth | string | null
    {
        return MaxWidth::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print')
                ->label('Print GRN Invoice')
                ->icon('heroicon-o-printer')
                ->color('warning')
                ->url(fn (): string => route('admin.grn.print', $this->record->id))
                ->openUrlInNewTab(),
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
