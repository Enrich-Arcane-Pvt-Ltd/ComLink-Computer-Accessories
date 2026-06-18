<?php

namespace App\Filament\Pages;

use App\Models\SystemSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Actions\Action;

class ManageSystemSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'System Management';
    protected static ?string $navigationLabel = 'System Settings';
    protected static ?string $title = 'System Settings';

    protected static string $view = 'filament.pages.manage-system-settings';

    public ?array $data = [];

    public function mount(): void
    {
        // පළමු Row එක ගන්නවා, නැත්නම් අලුතින් හදනවා
        $settings = SystemSetting::firstOrCreate();
        $this->form->fill($settings->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General Information')
                    ->schema([
                        Forms\Components\TextInput::make('shop_name')->required(),
                        Forms\Components\TextInput::make('currency_symbol')->label('Currency (e.g. LKR, $)')->required(),
                        Forms\Components\TextInput::make('shop_email')->email(),
                        Forms\Components\TextInput::make('shop_phone')->tel(),
                        Forms\Components\Textarea::make('shop_address')->columnSpanFull()->rows(5),
                    ])->columns(2),

                Forms\Components\Section::make('Location & Socials')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('location_lat')->label('Latitude'),
                                Forms\Components\TextInput::make('location_long')->label('Longitude'),
                            ]),

                        // Social Links (Repeater එකක් දැම්මාම ලේසියි)
                        Forms\Components\KeyValue::make('social_links')
                            ->label('Social Media Links')
                            ->keyLabel('Platform (e.g. Facebook)')
                            ->valueLabel('URL')
                            ->addButtonLabel('Add Social Link'),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Changes')
                ->submit('save')
                ->color('primary'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $settings = SystemSetting::first();
        $settings->update($data);

        Notification::make()
            ->success()
            ->title('Settings saved successfully')
            ->send();
    }
}
