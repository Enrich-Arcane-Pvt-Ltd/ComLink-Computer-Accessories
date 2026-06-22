<?php

namespace App\Filament\Resources\GalleryItemResource\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;

class GalleryItemTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Thumbnail')
                    ->disk('public')
                    ->square(),

                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Untitled Item'),

                TextColumn::make('external_link')
                    ->label('Link')
                    ->limit(30)
                    ->placeholder('No External Link'),

                IconColumn::make('is_visible')
                    ->label('In Gallery')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_featured_home')
                    ->label('On Home') // Home එකේ තියෙනවද කියලා ටේබල් එකෙන්ම බලාගන්න පුළුවන්
                    ->boolean()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                TernaryFilter::make('is_visible')
                    ->label('Gallery Visibility'),

                TernaryFilter::make('is_featured_home')
                    ->label('Featured on Home'),
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
