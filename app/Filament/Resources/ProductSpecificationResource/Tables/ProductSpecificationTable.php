<?php

namespace App\Filament\Resources\ProductSpecificationResource\Tables;

use App\Models\Category;
use Filament\Tables;
use Filament\Tables\Table;

class ProductSpecificationTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable()
                    ->weight('bold')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('subCategory.name')
                    ->label('Sub Category')
                    ->sortable()
                    ->searchable()
                    ->placeholder('— (All / Whole Category)')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('main_specs')
                    ->label('Main Specification Titles')
                    ->badge()
                    ->color('warning')
                    ->separator(',')
                    ->placeholder('No main specifications defined')
                    ->wrap(),

                Tables\Columns\TextColumn::make('general_specs')
                    ->label('Technical Specification -> General Specification Titles')
                    ->badge()
                    ->color('info')
                    ->separator(',')
                    ->placeholder('—')
                    ->wrap(),

                Tables\Columns\TextColumn::make('performance_specs')
                    ->label('Technical Specification -> Technical Performance Titles')
                    ->badge()
                    ->color('danger')
                    ->separator(',')
                    ->placeholder('—')
                    ->wrap(),

                Tables\Columns\TextColumn::make('compatibility_specs')
                    ->label('Technical Specification -> Compatibility & Standards Titles')
                    ->badge()
                    ->color('success')
                    ->separator(',')
                    ->placeholder('—')
                    ->wrap(),
            ])
            ->defaultSort('category_id')
            ->defaultGroup(
                Tables\Grouping\Group::make('category.name')
                    ->label('Category')
                    ->collapsible()
            )
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Filter by Category')
                    ->options(fn () => Category::whereNull('parent_id')->orderBy('sort_order')->pluck('name', 'id')),

                Tables\Filters\SelectFilter::make('sub_category_id')
                    ->label('Filter by Sub Category')
                    ->options(fn () => Category::whereNotNull('parent_id')->orderBy('name')->pluck('name', 'id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->slideOver(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No Product Specification Attributes Defined')
            ->emptyStateDescription('Click "+ Add Specification Attributes" to configure attributes for a Category and Sub Category.');
    }
}
