<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->hidden(fn() => $this->record->id === 1),
        ];
    }

    public function mount($record): void
    {
        parent::mount($record);

        if ($this->record->id === 1) {
            Notification::make()
                ->title('Access Denied')
                ->body('You cannot edit the Super Admin account.')
                ->danger()
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
        }
    }
}
