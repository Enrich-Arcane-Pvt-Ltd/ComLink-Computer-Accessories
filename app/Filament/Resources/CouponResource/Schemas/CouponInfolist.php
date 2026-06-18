<?php

namespace App\Filament\Resources\CouponResource\Schemas;

use App\Models\SystemSetting;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class CouponInfolist
{
    public static function infolist(Infolist $infolist): Infolist
    {
        $currency = SystemSetting::first()->currency_symbol ?? 'LKR';

        return $infolist
            ->schema([
                // Section 1: Coupon Summary
                Infolists\Components\Section::make('Coupon Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('code')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold')
                            ->copyable()
                            ->icon('heroicon-m-ticket'),

                        Infolists\Components\TextEntry::make('value')
                            ->label('Discount')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->color('primary')
                            ->formatStateUsing(
                                fn($record) =>
                                $record->type === 'percent'
                                    ? $record->value . '%'
                                    : $currency . ' ' . number_format($record->value, 2)
                            ),

                        Infolists\Components\TextEntry::make('is_active')
                            ->label('Status')
                            ->badge()
                            ->color(fn(bool $state) => $state ? 'success' : 'danger')
                            ->formatStateUsing(fn(bool $state) => $state ? 'Active' : 'Inactive'),
                    ])->columns(3),

                // Section 2: Restrictions
                Infolists\Components\Section::make('Restrictions')
                    ->schema([
                        Infolists\Components\TextEntry::make('min_spend')
                            ->label('Minimum Spend')
                            ->money($currency)
                            ->placeholder('None'),

                        Infolists\Components\TextEntry::make('products.name')
                            ->label('Valid Products')
                            ->badge()
                            ->color('info')
                            ->separator(', ')
                            ->placeholder('All Products'),

                        Infolists\Components\TextEntry::make('categories.name')
                            ->label('Valid Categories')
                            ->badge()
                            ->color('warning')
                            ->separator(', ')
                            ->placeholder('All Categories'),
                        Infolists\Components\TextEntry::make('collections.title')
                            ->label('Valid Collections')
                            ->badge()
                            ->color('success') // වෙනසක් පෙනෙන්න පාට වෙනස් කළා
                            ->separator(', ')
                            ->placeholder('All Collections'),
                    ])->columns(3),

                // Section 3: Usage & Validity
                Infolists\Components\Section::make('Usage & Validity')
                    ->schema([
                        Infolists\Components\TextEntry::make('usage_summary')
                            ->label('Usage Count')
                            ->state(fn($record) => $record->used_count . ' used out of ' . ($record->usage_limit ?? 'Unlimited')),

                        Infolists\Components\TextEntry::make('usage_limit_per_user')
                            ->label('Limit Per User'),

                        Infolists\Components\TextEntry::make('starts_at')
                            ->dateTime(),

                        Infolists\Components\TextEntry::make('ends_at')
                            ->dateTime()
                            ->color(fn($state) => $state && now()->gt($state) ? 'danger' : 'success'),
                    ])->columns(4),
            ]);
    }
}
