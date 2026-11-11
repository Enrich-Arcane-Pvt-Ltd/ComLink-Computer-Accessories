<?php

namespace App\Filament\Resources\SubCategoryResource\Tables;

use Filament\Tables;
use Filament\Tables\Table;

class SubCategoryTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Category Name')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\ImageColumn::make('image')
                    ->label('Sub Category Image')
                    ->circular()
                    ->state(fn (\App\Models\Category $record): ?string => $record->image_url)
                    ->defaultImageUrl(asset('images/default-subcategory-image.png'))
                    ->extraImgAttributes(['onerror' => "this.src='" . asset('images/default-subcategory-image.png') . "';"]),

                Tables\Columns\TextColumn::make('name')
                    ->label('Sub Category Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\ToggleColumn::make('is_visible')
                    ->label('Visibility')
                    ->onColor('success')
                    ->offColor('danger')
                    ->tooltip('Control whether this subcategory is visible on the web')
                    ->sortable(),
            ])
            ->defaultSort('parent_id', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Filter by Main Category')
                    ->relationship('parent', 'name', modifyQueryUsing: fn ($query) => $query->whereNull('parent_id'))
                    ->preload(),

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
