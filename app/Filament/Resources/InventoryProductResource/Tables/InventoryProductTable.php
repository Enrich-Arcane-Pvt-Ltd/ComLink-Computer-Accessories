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
                Tables\Filters\Filter::make('product_filter')
                    ->columnSpanFull()
                    ->form([
                        Forms\Components\Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 4,
                        ])->schema([
                            Forms\Components\Select::make('category_id')
                                ->label('Category')
                                ->placeholder('All Categories')
                                ->options(fn () => Category::whereNull('parent_id')->orderBy('sort_order')->pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(fn (Forms\Set $set) => $set('sub_category_id', null)),

                            Forms\Components\Select::make('sub_category_id')
                                ->label('Sub Category')
                                ->placeholder('All Sub Categories')
                                ->options(function (Forms\Get $get) {
                                    $parentId = $get('category_id');
                                    if ($parentId) {
                                        return Category::where('parent_id', $parentId)->orderBy('name')->pluck('name', 'id');
                                    }

                                    return Category::whereNotNull('parent_id')->orderBy('name')->pluck('name', 'id');
                                })
                                ->searchable()
                                ->preload(),

                            Forms\Components\Select::make('brand_id')
                                ->label('Brand')
                                ->placeholder('All Brands')
                                ->options(fn () => Brand::orderBy('name')->pluck('name', 'id'))
                                ->searchable()
                                ->preload(),

                            Forms\Components\Select::make('condition')
                                ->label('Condition')
                                ->placeholder('All Conditions')
                                ->options([
                                    'Brand New' => 'Brand New',
                                    'Used' => 'Used',
                                ]),
                        ]),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['sub_category_id'] ?? null, function ($q, $subId) {
                                $q->whereHas('storefrontProduct.categories', fn ($sq) => $sq->where('categories.id', $subId));
                            })
                            ->when(($data['category_id'] ?? null) && empty($data['sub_category_id']), function ($q, $catId) {
                                $q->whereHas('storefrontProduct.categories', function ($sq) use ($catId) {
                                    $sq->where('categories.id', $catId)
                                        ->orWhere('categories.parent_id', $catId);
                                });
                            })
                            ->when($data['brand_id'] ?? null, function ($q, $brandId) {
                                $q->whereHas('storefrontProduct', fn ($sq) => $sq->where('brand_id', $brandId));
                            })
                            ->when($data['condition'] ?? null, function ($q, $cond) {
                                $q->whereHas('storefrontProduct', fn ($sq) => $sq->where('condition', $cond));
                            });
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['category_id'] ?? null) {
                            $catName = Category::find($data['category_id'])?->name;
                            if ($catName) {
                                $indicators[] = Tables\Filters\Indicator::make("Category: {$catName}")
                                    ->removeField('category_id');
                            }
                        }
                        if ($data['sub_category_id'] ?? null) {
                            $subName = Category::find($data['sub_category_id'])?->name;
                            if ($subName) {
                                $indicators[] = Tables\Filters\Indicator::make("Sub Category: {$subName}")
                                    ->removeField('sub_category_id');
                            }
                        }
                        if ($data['brand_id'] ?? null) {
                            $brandName = Brand::find($data['brand_id'])?->name;
                            if ($brandName) {
                                $indicators[] = Tables\Filters\Indicator::make("Brand: {$brandName}")
                                    ->removeField('brand_id');
                            }
                        }
                        if ($data['condition'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make("Condition: {$data['condition']}")
                                ->removeField('condition');
                        }

                        return $indicators;
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(1)
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
