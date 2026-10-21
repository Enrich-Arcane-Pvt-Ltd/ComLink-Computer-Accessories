<?php

namespace App\Filament\Resources\ActivityLogResource;

use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'System Management';

    protected static ?string $pluralLabel = 'Audit Logs';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('causer.name')
                    ->label('User')
                    ->searchable()
                    ->placeholder('System'),

                Tables\Columns\TextColumn::make('description')
                    ->label('Action')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        'logged_in' => 'info',
                        'logged_out' => 'gray',
                        default => 'primary',
                    }),

                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Subject')
                    ->formatStateUsing(function ($state) {
                        return $state ? class_basename($state) : 'System';
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('properties.ip')
                    ->label('IP Address'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Activity Summary')
                    ->schema([
                        Infolists\Components\TextEntry::make('causer.name')
                            ->label('User')
                            ->icon('heroicon-m-user')
                            ->placeholder('System / Unknown'),

                        Infolists\Components\TextEntry::make('description')
                            ->label('Action Taken')
                            ->badge(),

                        Infolists\Components\TextEntry::make('subject_type')
                            ->label('Subject Model'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Timestamp')
                            ->dateTime(),
                    ])->columns(2),

                Infolists\Components\Section::make('Data Changes')
                    ->schema([
                        // මෙතන තිබුන ->color('danger') අයින් කළා
                        Infolists\Components\KeyValueEntry::make('properties.old')
                            ->label('Old Values (Before)')
                            ->keyLabel('Field')
                            ->valueLabel('Value'),

                        // මෙතන තිබුන ->color('success') අයින් කළා
                        Infolists\Components\KeyValueEntry::make('properties.attributes')
                            ->label('New Values (After)')
                            ->keyLabel('Field')
                            ->valueLabel('Value'),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record->properties && ($record->properties->has('old') || $record->properties->has('attributes'))),

                Infolists\Components\Section::make('Technical Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('properties.ip')
                            ->label('IP Address'),

                        Infolists\Components\TextEntry::make('properties.agent')
                            ->label('User Agent (Browser)'),
                    ])->columns(2)
                    ->collapsed(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageActivityLogs::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }
}
