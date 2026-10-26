<?php

declare(strict_types=1);

namespace App\Filament\Resources\InventoryResource\Tables;

use App\Models\InventoryProduct;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;

class InventoryTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('No')
                    ->rowIndex()
                    ->extraCellAttributes(['style' => 'vertical-align: top; padding-top: 10px; padding-bottom: 10px;']),

                Tables\Columns\ImageColumn::make('product_image')
                    ->label('Image')
                    ->getStateUsing(fn (InventoryProduct $record): ?string => $record->product_image
                        ?? $record->storefrontProduct?->images[0]
                        ?? null
                    )
                    ->disk('public')
                    ->defaultImageUrl('/images/placeholder.png')
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover ring-1 ring-slate-200 dark:ring-slate-700'])
                    ->extraCellAttributes(['style' => 'vertical-align: top; padding-top: 10px; padding-bottom: 10px;']),

                Tables\Columns\ViewColumn::make('product_info')
                    ->label('Product Details')
                    ->view('filament.tables.columns.inventory-single-product-name')
                    ->searchable(query: fn ($query, $search) => $query->where('product_name', 'like', "%{$search}%")
                        ->orWhere('product_code', 'like', "%{$search}%")
                    )
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('product_name', $direction))
                    ->extraCellAttributes(['style' => 'vertical-align: top; padding-top: 10px; padding-bottom: 10px; min-width: 250px;']),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-truck')
                    ->iconColor('success')
                    ->weight('medium')
                    ->extraCellAttributes(['style' => 'vertical-align: top; padding-top: 10px; padding-bottom: 10px; min-width: 140px;']),

                Tables\Columns\ViewColumn::make('stock_counts')
                    ->label('Inventory Stock')
                    ->view('filament.tables.columns.inventory-stock-counts')
                    ->extraCellAttributes(['style' => 'vertical-align: top; padding-top: 10px; padding-bottom: 10px; min-width: 160px;']),

                Tables\Columns\TextColumn::make('last_grn_date')
                    ->label('Last Stock Added')
                    ->getStateUsing(fn (InventoryProduct $record): string => $record->grnItems()->latest('id')->first()?->created_at?->format('M d, Y h:i A') ?? 'N/A')
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('created_at', $direction))
                    ->extraCellAttributes(['style' => 'vertical-align: top; padding-top: 10px; padding-bottom: 10px;']),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordAction('stock_history')
            ->actions([
                Tables\Actions\Action::make('stock_history')
                    ->label('Stock History')
                    ->tooltip('View Complete Stock-Wise History')
                    ->icon('heroicon-o-clock')
                    ->iconButton()
                    ->color('primary')
                    ->modalHeading(fn (InventoryProduct $record) => "Stock-Wise History — {$record->product_name}")
                    ->modalWidth(MaxWidth::SevenExtraLarge)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn (InventoryProduct $record) => view('filament.pages.stock-report-modal', [
                        'reportData' => app(\App\Services\StockManagementService::class)->generateReportForProduct($record),
                    ])),

                Tables\Actions\ViewAction::make()
                    ->iconButton()
                    ->tooltip('View Inventory Details'),

                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit Product Details'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
