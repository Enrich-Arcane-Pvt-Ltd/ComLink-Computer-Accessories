<?php

namespace App\Filament\Resources\ProductResource\Schemas;

use App\Models\SystemSetting;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ProductInfolist
{
    public static function infolist(Infolist $infolist): Infolist
    {
        // Database එකෙන් Currency එක ගන්නවා
        $currency = SystemSetting::first()->currency_symbol ?? 'LKR';

        return $infolist
            ->schema([
                // 1. මූලික විස්තර සහ පින්තූර
                Infolists\Components\Section::make('Product Overview')
                    ->schema([
                        // Product Images (Gallery)
                        Infolists\Components\ImageEntry::make('images')
                            ->label('Product Gallery (4 Photos)')
                            ->disk('public')
                            ->circular(false)
                            ->height(100)
                            ->limit(4)
                            ->extraImgAttributes(['style' => 'object-fit: contain; background: #fff; border: 1px solid #eee; border-radius: 6px;']),
                        // Infolists\Components\ViewEntry::make('images')
                        //     ->label('Product Gallery')
                        //     ->view('filament.components.gallery') // අපි හදපු Blade File එක
                        //     ->columnSpanFull(),

                        Infolists\Components\Group::make()
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->weight('bold')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),

                                Infolists\Components\TextEntry::make('sku')
                                    ->label('Main SKU')
                                    ->copyable()
                                    ->icon('heroicon-m-qr-code')
                                    ->placeholder('No SKU'),

                                Infolists\Components\TextEntry::make('brand.name')
                                    ->label('Brand')
                                    ->badge()
                                    ->color('gray'),

                                Infolists\Components\TextEntry::make('price')
                                    ->label('Price')
                                    ->weight('bold')
                                    ->formatStateUsing(function ($record) use ($currency) {
                                        if ($record->type === 'variable') {
                                            $min = $record->variants->min('price');
                                            $max = $record->variants->max('price');

                                            if (! $min) {
                                                return 'N/A';
                                            }

                                            if ($min == $max) {
                                                return $currency.' '.number_format($min, 2);
                                            }

                                            return $currency.' '.number_format($min, 2).' - '.number_format($max, 2);
                                        }

                                        return $currency.' '.number_format($record->price, 2);
                                    }),
                                Infolists\Components\TextEntry::make('quantity')
                                    ->label('Total Stock')
                                    ->badge()
                                    ->color(fn ($state) => $state <= 0 ? 'danger' : ($state <= 10 ? 'warning' : 'success'))
                                    ->formatStateUsing(function ($record) {
                                        if ($record->type === 'variable') {
                                            return $record->variants->sum('stock');
                                        }

                                        return $record->quantity;
                                    }),
                            ])->columns(2),
                    ])->columns(2),

                // 2. විස්තරය (Description)
                Infolists\Components\Section::make('Description')
                    ->schema([
                        Infolists\Components\TextEntry::make('description')
                            ->prose()
                            ->markdown()
                            ->hiddenLabel(),
                    ])
                    ->collapsed(),

                // 3. සම්බන්ධතා (Categories & Collections)
                Infolists\Components\Section::make('Organization')
                    ->schema([
                        Infolists\Components\TextEntry::make('categories.name')
                            ->label('Categories')
                            ->badge()
                            ->color('info')
                            ->separator(', '),

                        Infolists\Components\TextEntry::make('collections.title')
                            ->label('Collections')
                            ->badge()
                            ->color('warning')
                            ->separator(', '),
                    ])->columns(2),

                // 4. Variants (පාට සහ Sizes)
                Infolists\Components\Section::make('Product Variants')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('variants')
                            ->label('')
                            ->schema([
                                Infolists\Components\Grid::make(4)
                                    ->schema([
                                        Infolists\Components\ImageEntry::make('image')
                                            ->label('Variant Image')
                                            ->circular()
                                            ->defaultImageUrl(url('/images/placeholder.png')),

                                        Infolists\Components\TextEntry::make('attributes')
                                            ->label('Options')
                                            ->badge()
                                            ->formatStateUsing(function ($state) {
                                                if (is_array($state)) {
                                                    $formatted = [];
                                                    foreach ($state as $item) {
                                                        if (isset($item['name']) && isset($item['value'])) {
                                                            $formatted[] = "{$item['name']}: {$item['value']}";
                                                        } else {
                                                            return collect($state)
                                                                ->map(fn ($val, $key) => "$key: $val")
                                                                ->join(' | ');
                                                        }
                                                    }

                                                    return implode(' | ', $formatted);
                                                }

                                                return '';
                                            }),

                                        Infolists\Components\TextEntry::make('sku')
                                            ->label('SKU'),

                                        Infolists\Components\TextEntry::make('stock')
                                            ->label('Stock')
                                            ->badge()
                                            ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
                                    ]),
                            ])
                            ->grid(1), // මෙතන තිබුණු ->containable(false) පේළිය අයින් කළා.
                    ])
                    ->hidden(fn ($record) => $record->type === 'simple'),
            ]);
    }
}
