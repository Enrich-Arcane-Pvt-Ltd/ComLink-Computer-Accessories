<?php

namespace App\Filament\Resources\ProductResource\Tables;

use Filament\Tables;
use Filament\Tables\Table;

class ProductTable
{
    public static function table(Table $table): Table
    {
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

                Tables\Columns\ViewColumn::make('web_show_count')
                    ->label('Web Show Count')
                    ->view('filament.tables.columns.web-show-count')
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('quantity', $direction))
                    ->tooltip('Click to adjust web show count')
                    ->extraCellAttributes(['style' => 'vertical-align: top; padding-top: 10px; padding-bottom: 10px; min-width: 140px;'])
                    ->action(
                        Tables\Actions\Action::make('adjust_web_count_column')
                            ->modalHeading(fn (\App\Models\Product $record) => "Adjust Web Show Count - {$record->name}")
                            ->modalDescription('Transfer stock between Warehouse Inventory and Web Storefront.')
                            ->modalSubmitActionLabel('Update Stock Allocation')
                            ->form(function (\App\Models\Product $record) {
                                $invStock = $record->product_code
                                    ? (int) \App\Models\GrnItem::where('product_code', $record->product_code)->sum('product_quantity')
                                    : (int) ($record->inventory_stock ?? $record->quantity ?? 0);
                                $currentShow = (int) ($record->show_quantity ?? $record->quantity ?? 0);
                                $currentAvailable = (int) ($record->quantity ?? 0);
                                $currentBalance = max(0, $invStock - $currentShow);

                                return [
                                    \Filament\Forms\Components\Grid::make(2)
                                        ->schema([
                                            \Filament\Forms\Components\Placeholder::make('total_inventory')
                                                ->label('Total Inventory Stock (GRN)')
                                                ->content("{$invStock} units"),
                                            \Filament\Forms\Components\Placeholder::make('current_balance')
                                                ->label('Current Warehouse Balance')
                                                ->content("{$currentBalance} units"),
                                            \Filament\Forms\Components\Placeholder::make('current_show')
                                                ->label('Current Web Show Count')
                                                ->content("{$currentShow} units"),
                                            \Filament\Forms\Components\Placeholder::make('current_available')
                                                ->label('Current Available (Online)')
                                                ->content("{$currentAvailable} units"),
                                        ]),

                                    \Filament\Forms\Components\TextInput::make('new_show_quantity')
                                        ->label('New Web Show Count')
                                        ->numeric()
                                        ->required()
                                        ->default($currentShow)
                                        ->minValue(0)
                                        ->maxValue($invStock)
                                        ->helperText("Must be between 0 and {$invStock} units (Total Inventory Stock).")
                                        ->live()
                                        ->afterStateUpdated(function (\Filament\Forms\Set $set, $state) use ($invStock) {
                                            $newShow = (int) $state;
                                            $newBalance = max(0, $invStock - $newShow);
                                            $set('projected_balance', "{$newBalance} units remaining in warehouse");
                                        }),

                                    \Filament\Forms\Components\Placeholder::make('projected_balance')
                                        ->label('Projected Warehouse Balance')
                                        ->content(function (\Filament\Forms\Get $get) use ($invStock, $currentShow) {
                                            $newShow = (int) ($get('new_show_quantity') ?? $currentShow);
                                            $newBalance = max(0, $invStock - $newShow);

                                            return "{$newBalance} units remaining in warehouse";
                                        }),
                                ];
                            })
                            ->action(function (\App\Models\Product $record, array $data) {
                                $newShow = (int) $data['new_show_quantity'];
                                $oldShow = (int) ($record->show_quantity ?? $record->quantity ?? 0);
                                $diff = $newShow - $oldShow;

                                $currentAvailable = (int) ($record->quantity ?? 0);
                                $newAvailable = max(0, min($newShow, $currentAvailable + $diff));

                                $invStock = $record->product_code
                                    ? (int) \App\Models\GrnItem::where('product_code', $record->product_code)->sum('product_quantity')
                                    : (int) ($record->inventory_stock ?? 0);

                                $record->update([
                                    'show_quantity' => $newShow,
                                    'quantity' => $newAvailable,
                                    'inventory_stock' => $invStock,
                                ]);

                                $newBalance = max(0, $invStock - $newShow);

                                \Filament\Notifications\Notification::make()
                                    ->title('Stock Allocation Updated')
                                    ->body("Web Show Count set to {$newShow}. Balance Inventory Stock is now {$newBalance} units.")
                                    ->success()
                                    ->send();
                            })
                    ),

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
                Tables\Actions\Action::make('adjust_web_count')
                    ->label('Adjust Web Count')
                    ->tooltip('Adjust Web Show Count')
                    ->icon('heroicon-o-arrows-right-left')
                    ->iconButton()
                    ->color('info')
                    ->modalHeading(fn (\App\Models\Product $record) => "Adjust Web Show Count - {$record->name}")
                    ->modalDescription('Transfer stock between Warehouse Inventory and Web Storefront.')
                    ->modalSubmitActionLabel('Update Stock Allocation')
                    ->form(function (\App\Models\Product $record) {
                        $invStock = $record->product_code
                            ? (int) \App\Models\GrnItem::where('product_code', $record->product_code)->sum('product_quantity')
                            : (int) ($record->inventory_stock ?? $record->quantity ?? 0);
                        $currentShow = (int) ($record->show_quantity ?? $record->quantity ?? 0);
                        $currentAvailable = (int) ($record->quantity ?? 0);
                        $currentBalance = max(0, $invStock - $currentShow);

                        return [
                            \Filament\Forms\Components\Grid::make(2)
                                ->schema([
                                    \Filament\Forms\Components\Placeholder::make('total_inventory')
                                        ->label('Total Inventory Stock (GRN)')
                                        ->content("{$invStock} units"),
                                    \Filament\Forms\Components\Placeholder::make('current_balance')
                                        ->label('Current Warehouse Balance')
                                        ->content("{$currentBalance} units"),
                                    \Filament\Forms\Components\Placeholder::make('current_show')
                                        ->label('Current Web Show Count')
                                        ->content("{$currentShow} units"),
                                    \Filament\Forms\Components\Placeholder::make('current_available')
                                        ->label('Current Available (Online)')
                                        ->content("{$currentAvailable} units"),
                                ]),

                            \Filament\Forms\Components\TextInput::make('new_show_quantity')
                                ->label('New Web Show Count')
                                ->numeric()
                                ->required()
                                ->default($currentShow)
                                ->minValue(0)
                                ->maxValue($invStock)
                                ->helperText("Must be between 0 and {$invStock} units (Total Inventory Stock).")
                                ->live()
                                ->afterStateUpdated(function (\Filament\Forms\Set $set, $state) use ($invStock) {
                                    $newShow = (int) $state;
                                    $newBalance = max(0, $invStock - $newShow);
                                    $set('projected_balance', "{$newBalance} units remaining in warehouse");
                                }),

                            \Filament\Forms\Components\Placeholder::make('projected_balance')
                                ->label('Projected Warehouse Balance')
                                ->content(function (\Filament\Forms\Get $get) use ($invStock, $currentShow) {
                                    $newShow = (int) ($get('new_show_quantity') ?? $currentShow);
                                    $newBalance = max(0, $invStock - $newShow);

                                    return "{$newBalance} units remaining in warehouse";
                                }),
                        ];
                    })
                    ->action(function (\App\Models\Product $record, array $data) {
                        $newShow = (int) $data['new_show_quantity'];
                        $oldShow = (int) ($record->show_quantity ?? $record->quantity ?? 0);
                        $diff = $newShow - $oldShow;

                        $currentAvailable = (int) ($record->quantity ?? 0);
                        $newAvailable = max(0, min($newShow, $currentAvailable + $diff));

                        $invStock = $record->product_code
                            ? (int) \App\Models\GrnItem::where('product_code', $record->product_code)->sum('product_quantity')
                            : (int) ($record->inventory_stock ?? 0);

                        $record->update([
                            'show_quantity' => $newShow,
                            'quantity' => $newAvailable,
                            'inventory_stock' => $invStock,
                        ]);

                        $newBalance = max(0, $invStock - $newShow);

                        \Filament\Notifications\Notification::make()
                            ->title('Stock Allocation Updated')
                            ->body("Web Show Count set to {$newShow}. Balance Inventory Stock is now {$newBalance} units.")
                            ->success()
                            ->send();
                    }),
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
