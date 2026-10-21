<?php

namespace App\Filament\Resources\InventoryResource\Tables;

use App\Models\GrnItem;
use App\Models\InventoryProduct;
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
                    ->rowIndex(),

                Tables\Columns\ImageColumn::make('product_image')
                    ->label('Product Image')
                    ->getStateUsing(fn (GrnItem $record): ?string => $record->inventoryProduct?->product_image
                        ?? InventoryProduct::where('product_code', $record->product_code)->value('product_image')
                    )
                    ->disk('public')
                    ->defaultImageUrl('/images/placeholder.png')
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover']),

                Tables\Columns\ViewColumn::make('product_name')
                    ->label('Product Name')
                    ->view('filament.tables.columns.inventory-product-name')
                    ->searchable(query: fn ($query, $search) => $query->where('product_name', 'like', "%{$search}%")
                        ->orWhere('product_code', 'like', "%{$search}%")
                    )
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('product_name', $direction)),

                Tables\Columns\ViewColumn::make('total')
                    ->label('Total')
                    ->view('filament.tables.columns.inventory-product-total')
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('total', $direction)),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Product Added Date & Time')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),

                Tables\Columns\TextColumn::make('grn.supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-truck')
                    ->iconColor('success')
                    ->weight('medium'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('product_condition')
                    ->label('Condition')
                    ->options([
                        'Brandnew' => 'Brandnew',
                        'Used' => 'Used',
                    ]),
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
