<?php

namespace App\Filament\Resources\SupplierResource\Tables;

use Filament\Tables;
use Filament\Tables\Table;

class SupplierTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Supplier Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-building-storefront')
                    ->iconColor('primary'),

                Tables\Columns\TextColumn::make('contact_no')
                    ->label('Contact No')
                    ->searchable()
                    ->copyable()
                    ->placeholder('—')
                    ->icon('heroicon-o-phone')
                    ->iconColor('success'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->placeholder('—')
                    ->icon('heroicon-o-envelope')
                    ->iconColor('warning'),

                Tables\Columns\TextColumn::make('address')
                    ->label('Address')
                    ->limit(45)
                    ->placeholder('—')
                    ->tooltip(fn ($record): ?string => $record->address),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Added Date')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
