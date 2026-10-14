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
                    ->label('Image')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder.png'))
                    ->extraImgAttributes(['style' => 'background-color: white; padding: 2px;']),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                // Parent Category එක පෙන්නන Column එක
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Parent Category')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_visible')
                    ->label('Visibility')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable(),
            ])
            ->defaultSort('sort_order', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_visible')
                    ->label('Visibility')
                    ->trueLabel('Visible Only')
                    ->falseLabel('Hidden Only'),

                // Main Categories Filter
                Tables\Filters\Filter::make('main_categories')
                    ->label('Main Categories Only')
                    ->query(fn($query) => $query->whereNull('parent_id')),

                // Subcategories Filter
                Tables\Filters\Filter::make('sub_categories')
                    ->label('Subcategories Only')
                    ->query(fn($query) => $query->whereNotNull('parent_id')),

                // Filter by Parent Category
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Filter by Parent')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload(),
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
