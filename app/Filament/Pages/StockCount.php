<?php

namespace App\Filament\Pages;

use App\Models\Product;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockCount extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationGroup = 'Stock Management';

    protected static ?string $navigationLabel = 'Stock Count';

    protected static ?string $title = 'Stock Count';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.stock-count';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->with(['supplier', 'brand', 'grnItems', 'latestGrnItem'])
                    ->latest('id')
            )
            ->columns([
                Tables\Columns\TextColumn::make('row_index')
                    ->label('No')
                    ->rowIndex()
                    ->width('50px')
                    ->alignCenter(),

                Tables\Columns\ViewColumn::make('product_details')
                    ->label('Product Details')
                    ->view('filament.tables.columns.stock-count-product-details')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                              ->orWhere('product_code', 'like', "%{$search}%");
                        });
                    }),

                Tables\Columns\TextColumn::make('inventory_full_count')
                    ->label('Inventory Full Count')
                    ->alignCenter()
                    ->formatStateUsing(fn ($record) => number_format((int) $record->inventory_full_count) . ' units')
                    ->weight('bold')
                    ->color('primary')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('inventory_stock', $direction);
                    }),

                Tables\Columns\ViewColumn::make('current_inventory_stock')
                    ->label('Current Inventory Stock')
                    ->view('filament.tables.columns.stock-count-current-stock')
                    ->alignCenter(),

                Tables\Columns\ViewColumn::make('sold_count')
                    ->label('Sold Count')
                    ->view('filament.tables.columns.stock-count-sold')
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('condition')
                    ->label('Condition')
                    ->options([
                        'Brand New' => 'Brand New',
                        'Used' => 'Used',
                        'Refurbished' => 'Refurbished',
                    ]),

                Tables\Filters\SelectFilter::make('supplier_id')
                    ->label('Supplier')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('stock_status')
                    ->form([
                        \Filament\Forms\Components\Select::make('status')
                            ->label('Stock Status')
                            ->options([
                                'in_stock' => 'In Stock (> 5 units)',
                                'low_stock' => 'Low Stock (1 - 5 units)',
                                'out_of_stock' => 'Out of Stock (0 units)',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['status'])) {
                            if ($data['status'] === 'out_of_stock') {
                                return $query->where('quantity', '<=', 0);
                            }
                            if ($data['status'] === 'low_stock') {
                                return $query->where('quantity', '>', 0)->where('quantity', '<=', 5);
                            }
                            if ($data['status'] === 'in_stock') {
                                return $query->where('quantity', '>', 5);
                            }
                        }

                        return $query;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view_product')
                    ->label('View Details')
                    ->icon('heroicon-m-eye')
                    ->iconButton()
                    ->tooltip('View Product Catalog Details')
                    ->color('gray')
                    ->url(fn (Product $record): string => route('filament.admin.resources.product-resource.products.view', ['record' => $record])),
            ])
            ->defaultPaginationPageOption(10);
    }
}
