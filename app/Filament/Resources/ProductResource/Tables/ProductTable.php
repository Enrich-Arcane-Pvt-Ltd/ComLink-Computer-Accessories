<?php

namespace App\Filament\Resources\ProductResource\Tables;

use Filament\Tables;
use Filament\Tables\Table;

class ProductTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['supplier', 'brand', 'categories']))
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),

                Tables\Columns\ViewColumn::make('product_info')
                    ->label('Product')
                    ->view('filament.tables.columns.product-info')
                    ->searchable(query: fn ($query, $search) => $query->where('name', 'like', "%{$search}%")),

                Tables\Columns\ViewColumn::make('supplier_details')
                    ->label('Supplier Details')
                    ->view('filament.tables.columns.supplier-details')
                    ->searchable(query: fn ($query, $search) => $query->whereHas('supplier', fn ($q) => $q->where('name', 'like', "%{$search}%"))),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Available Product Count')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state < 10 => 'warning',
                        default => 'success',
                    }),

                Tables\Columns\ViewColumn::make('price')
                    ->label('Latest Price')
                    ->view('filament.tables.columns.product-price')
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('price', $direction)),

                Tables\Columns\ToggleColumn::make('is_visible')
                    ->label('Availability')
                    ->onColor('success')
                    ->offColor('danger')
                    ->tooltip('Control product display on the storefront')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('supplier')
                    ->relationship('supplier', 'name')
                    ->preload(),

                Tables\Filters\SelectFilter::make('brand')
                    ->relationship('brand', 'name')
                    ->preload(),

                Tables\Filters\SelectFilter::make('condition')
                    ->options([
                        'Brand New' => 'Brand New',
                        'Used' => 'Used',
                    ]),

                Tables\Filters\TernaryFilter::make('is_visible')
                    ->label('Storefront Availability'),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Best Seller Only'),
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
