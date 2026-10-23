<?php

namespace App\Filament\Resources\ProductResource\Tables;

use Filament\Tables;
use Filament\Tables\Table;

class ProductTable
{
    public static function table(Table $table): Table
    {
        \App\Models\Product::syncAllProductAvailableQuantities();

        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['supplier', 'brand', 'categories', 'latestGrnItem', 'variants']))
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('No')
                    ->rowIndex()
                    ->extraCellAttributes(['style' => 'vertical-align: top; padding-top: 10px; padding-bottom: 10px;']),

                Tables\Columns\ViewColumn::make('product_info')
                    ->label('Product')
                    ->view('filament.tables.columns.product-info')
                    ->searchable(query: fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
                    ->extraCellAttributes(['style' => 'vertical-align: top; padding-top: 10px; padding-bottom: 10px; min-width: 220px;']),

                Tables\Columns\ViewColumn::make('supplier_details')
                    ->label('Supplier Details')
                    ->view('filament.tables.columns.supplier-details')
                    ->searchable(query: fn ($query, $search) => $query->whereHas('supplier', fn ($q) => $q->where('name', 'like', "%{$search}%")))
                    ->extraCellAttributes(['style' => 'vertical-align: top; padding-top: 10px; padding-bottom: 10px; min-width: 220px;']),

                Tables\Columns\ViewColumn::make('product_count')
                    ->label('Product Count')
                    ->view('filament.tables.columns.product-count')
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('quantity', $direction))
                    ->extraCellAttributes(['style' => 'vertical-align: top; padding-top: 10px; padding-bottom: 10px; min-width: 170px;']),

                Tables\Columns\ViewColumn::make('grn_price')
                    ->label('GRN Price')
                    ->view('filament.tables.columns.product-grn-price')
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('grn_price', $direction))
                    ->extraCellAttributes(['style' => 'vertical-align: top; padding-top: 10px; padding-bottom: 10px; min-width: 110px;']),

                Tables\Columns\ViewColumn::make('price')
                    ->label('Latest Price')
                    ->view('filament.tables.columns.product-price')
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('price', $direction))
                    ->extraCellAttributes(['style' => 'vertical-align: top; padding-top: 10px; padding-bottom: 10px; min-width: 180px;']),

                Tables\Columns\ToggleColumn::make('is_visible')
                    ->label('Availability')
                    ->onColor('success')
                    ->offColor('danger')
                    ->tooltip('Control product display on the storefront')
                    ->sortable()
                    ->extraCellAttributes(['style' => 'vertical-align: top; padding-top: 10px; padding-bottom: 10px;']),
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
                Tables\Actions\Action::make('report')
                    ->label('Report')
                    ->tooltip('Stock-Wise Report')
                    ->icon('heroicon-o-document-chart-bar')
                    ->iconButton()
                    ->color('info')
                    ->modalHeading(fn (\App\Models\Product $record) => "Stock-Wise Report — {$record->name}")
                    ->modalWidth(\Filament\Support\Enums\MaxWidth::SevenExtraLarge)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn (\App\Models\Product $record) => view('filament.pages.stock-report-modal', [
                        'reportData' => app(\App\Services\StockManagementService::class)->generateReportForProduct($record),
                    ])),
                Tables\Actions\ViewAction::make()
                    ->iconButton()
                    ->tooltip('View Product Details')
                    ->url(fn (\App\Models\Product $record): string => \App\Filament\Resources\ProductResource\ProductResource::getUrl('view', ['record' => $record])),
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit Product'),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Delete Product'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
