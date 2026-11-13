<?php

namespace App\Filament\Resources\CategoryResource\Tables;

use Filament\Tables;
use Filament\Tables\Table;

class CategoryTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Category Image')
                    ->circular()
                    ->state(fn (\App\Models\Category $record): ?string => $record->image_url)
                    ->defaultImageUrl(asset('images/default-category-image.png'))
                    ->extraImgAttributes(['onerror' => "this.src='" . asset('images/default-category-image.png') . "';"]),

                Tables\Columns\TextColumn::make('name')
                    ->label('Category Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\ToggleColumn::make('is_visible')
                    ->label('Visibility')
                    ->onColor('success')
                    ->offColor('danger')
                    ->tooltip('Toggle category visibility on the storefront')
                    ->sortable(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_visible')
                    ->label('Visibility Status'),
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
