<?php

namespace App\Filament\Resources\InventoryProductResource\Tables;

use App\Models\InventoryProduct;
use Filament\Tables;
use Filament\Tables\Table;

class InventoryProductTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['storefrontProduct.categories', 'storefrontProduct.brand', 'storefrontProduct.variants']))
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),

                Tables\Columns\ImageColumn::make('product_image')
                    ->label('Product Image')
                    ->getStateUsing(fn (InventoryProduct $record): ?string => $record->product_image
                        ?? $record->storefrontProduct?->images[0]
                        ?? null
                    )
                    ->disk('public')
                    ->defaultImageUrl('/images/placeholder.png')
                    ->circular(false)
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover']),

                Tables\Columns\TextColumn::make('product_code')
                    ->label('Product Code')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->fontFamily('mono')
                    ->copyable()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('product_name')
                    ->label('Product Name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->wrap(),

                Tables\Columns\TextColumn::make('category_info')
                    ->label('Category / Sub Category')
                    ->getStateUsing(function (InventoryProduct $record): string {
                        $product = $record->storefrontProduct;
                        if (! $product) {
                            return '—';
                        }
                        $main = $product->categories->firstWhere('parent_id', null);
                        $sub = $product->categories->firstWhere('parent_id', '!=', null);
                        if ($main && $sub) {
                            return "{$main->name} › {$sub->name}";
                        }

                        return $main?->name ?? $sub?->name ?? '—';
                    })
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('storefrontProduct.brand.name')
                    ->label('Brand')
                    ->placeholder('—')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('storefrontProduct.condition')
                    ->label('Condition')
                    ->default('Brand New')
                    ->badge()
                    ->color(fn (?string $state): string => $state === 'Used' ? 'warning' : 'success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Added Date')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->after(function (InventoryProduct $record) {
                        if ($record->product_code) {
                            \App\Models\Product::where('product_code', $record->product_code)->delete();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
