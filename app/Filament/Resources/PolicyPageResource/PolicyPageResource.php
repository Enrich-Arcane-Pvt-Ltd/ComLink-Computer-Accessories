<?php

namespace App\Filament\Resources\PolicyPageResource;

use App\Filament\Resources\PolicyPageResource\Pages;
use App\Models\PolicyPage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PolicyPageResource extends Resource
{
    protected static ?string $model = PolicyPage::class;

    protected static ?string $navigationGroup = 'Our Policies';

    protected static ?string $navigationLabel = 'Manage Policies';

    protected static ?string $modelLabel = 'Policy Page';

    protected static ?string $pluralModelLabel = 'Our Policies';

    protected static ?string $slug = 'our-policies';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Policy Page Details')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Page Heading / Title')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('last_updated')
                        ->label('Last Updated Label')
                        ->placeholder('e.g. Last updated: November 2026')
                        ->maxLength(120),

                    Forms\Components\TextInput::make('slug')
                        ->label('Public URL Path')
                        ->prefix('/')
                        ->disabled()
                        ->dehydrated(false),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->inline(false),
                ])
                ->columns(2),

            Forms\Components\Section::make('Policy Content')
                ->description('Edit the policy content below. Changes will immediately appear on the public website.')
                ->schema([
                    Forms\Components\RichEditor::make('content')
                        ->label('')
                        ->required()
                        ->toolbarButtons([
                            'h2',
                            'h3',
                            'bold',
                            'italic',
                            'underline',
                            'strike',
                            'bulletList',
                            'orderedList',
                            'link',
                            'blockquote',
                            'redo',
                            'undo',
                        ])
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Policy Title')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Public URL')
                    ->formatStateUsing(fn (string $state) => '/' . $state)
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('last_updated')
                    ->label('Last Updated Label')
                    ->placeholder('Not set'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Saved')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view_live')
                    ->label('View Live')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (PolicyPage $record) => url('/' . $record->slug), shouldOpenInNewTab: true),

                Tables\Actions\EditAction::make()
                    ->label('Edit Content'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPolicyPages::route('/'),
            'edit' => Pages\EditPolicyPage::route('/{record}/edit'),
        ];
    }
}
