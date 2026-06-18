<?php

namespace App\Filament\Resources\AttributeResource\Tables;

use Filament\Tables;
use Filament\Tables\Table;

class AttributeTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'color' => 'info',
                        'text' => 'success',
                        default => 'gray',
                    }),

                // Values කීයක් තියෙනවද කියලා පෙන්නනවා
                Tables\Columns\TextColumn::make('values_count')
                    ->counts('values')
                    ->label('Total Values'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
