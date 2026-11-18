<?php

namespace App\Filament\Resources\PolicyPageResource\Pages;

use App\Filament\Resources\PolicyPageResource\PolicyPageResource;
use App\Models\PolicyPage;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPolicyPage extends EditRecord
{
    protected static string $resource = PolicyPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_live')
                ->label('View Live Page')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('info')
                ->url(fn (PolicyPage $record) => url('/' . $record->slug), shouldOpenInNewTab: true),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
