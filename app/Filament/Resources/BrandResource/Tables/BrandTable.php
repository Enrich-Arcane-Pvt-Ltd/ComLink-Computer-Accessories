<?php

namespace App\Filament\Resources\BrandResource\Tables;

use Filament\Tables;
use Filament\Tables\Table;

class BrandTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder.png'))
                    ->extraImgAttributes(['style' => 'background-color: white; padding: 1px;']),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\ColorColumn::make('primary_hex')
                    ->label('Color'),

                Tables\Columns\IconColumn::make('is_visible')
                    ->boolean()
                    ->sortable()
                    ->label('Visibility'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->date()
                    ->sortable()
                    ->label('Last Updated'),
            ])
            ->defaultSort('sort_order', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_visible')
                    ->label('Visibility')
                    ->boolean()
                    ->trueLabel('Visible Brands')
                    ->falseLabel('Hidden Brands'),
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
