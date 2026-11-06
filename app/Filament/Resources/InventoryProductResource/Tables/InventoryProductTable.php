<?php

namespace App\Filament\Resources\InventoryProductResource\Tables;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryProduct;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
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
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->placeholder('All Categories')
                    ->options(fn () => Category::whereNull('parent_id')->orderBy('sort_order')->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->query(function ($query, array $data) {
                        if (! empty($data['value'])) {
                            $catId = $data['value'];
                            $query->whereHas('storefrontProduct.categories', function ($sq) use ($catId) {
                                $sq->where('categories.id', $catId)
                                    ->orWhere('categories.parent_id', $catId);
                            });
                        }
                    }),

                Tables\Filters\SelectFilter::make('sub_category_id')
                    ->label('Sub Category')
                    ->placeholder('All Sub Categories')
                    ->options(function () {
                        return Category::whereNotNull('parent_id')
                            ->with('parent')
                            ->get()
                            ->mapWithKeys(function ($sub) {
                                $prefix = $sub->parent ? "{$sub->parent->name} › " : '';

                                return [$sub->id => "{$prefix}{$sub->name}"];
                            })
                            ->sort()
                            ->all();
                    })
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->query(function ($query, array $data) {
                        if (! empty($data['value'])) {
                            $query->whereHas('storefrontProduct.categories', fn ($sq) => $sq->where('categories.id', $data['value']));
                        }
                    }),

                Tables\Filters\SelectFilter::make('brand_id')
                    ->label('Brand')
                    ->placeholder('All Brands')
                    ->options(fn () => Brand::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->query(function ($query, array $data) {
                        if (! empty($data['value'])) {
                            $query->whereHas('storefrontProduct', fn ($sq) => $sq->where('brand_id', $data['value']));
                        }
                    }),

                Tables\Filters\SelectFilter::make('condition')
                    ->label('Condition')
                    ->placeholder('All Conditions')
                    ->options([
                        'Brand New' => 'Brand New',
                        'Used' => 'Used',
                    ])
                    ->native(false)
                    ->query(function ($query, array $data) {
                        if (! empty($data['value'])) {
                            $query->whereHas('storefrontProduct', fn ($sq) => $sq->where('condition', $data['value']));
                        }
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns([
                'default' => 1,
                'sm' => 2,
                'lg' => 4,
            ])
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
