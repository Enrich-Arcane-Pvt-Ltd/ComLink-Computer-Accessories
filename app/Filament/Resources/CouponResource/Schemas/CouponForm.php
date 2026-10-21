<?php

namespace App\Filament\Resources\CouponResource\Schemas;

use App\Models\SystemSetting;
use Filament\Forms;
use Illuminate\Support\Str;

class CouponForm
{
    public static function schema(): array
    {
        $currency = SystemSetting::first()->currency_symbol ?? 'LKR';

        return [
            // කොටස 1: මූලික විස්තර
            Forms\Components\Section::make('Basic Information')
                ->icon('heroicon-m-ticket')
                ->schema([
                    Forms\Components\TextInput::make('code')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('code', Str::upper($state)))
                        ->placeholder('Ex: SUMMER2026'),

                    Forms\Components\Select::make('type')
                        ->options([
                            'fixed' => 'Fixed Amount ('.$currency.')',
                            'percent' => 'Percentage (%)',
                        ])
                        ->required()
                        ->default('fixed')
                        ->live(), // Type එක මාරු වෙනකොට යට Field එකේ Prefix එක මාරු කරන්න

                    Forms\Components\TextInput::make('value')
                        ->label('Discount Value')
                        ->required()
                        ->numeric()
                        ->prefix(fn (Forms\Get $get) => $get('type') === 'percent' ? '%' : $currency),
                ])->columns(3),

            // කොටස 2: සීමාවන් (Restrictions)
            Forms\Components\Section::make('Usage Restrictions')
                ->icon('heroicon-m-adjustments-horizontal')
                ->description('Limit this coupon to specific products, categories, or cart value.')
                ->schema([
                    Forms\Components\TextInput::make('min_spend')
                        ->label('Minimum Spend Amount')
                        ->numeric()
                        ->prefix($currency)
                        ->placeholder('No minimum'),

                    Forms\Components\Select::make('products')
                        ->relationship('products', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->label('Limit to Specific Products'),

                    Forms\Components\Select::make('categories')
                        ->relationship('categories', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->label('Limit to Specific Categories'),
                    Forms\Components\Select::make('collections')
                        ->relationship('collections', 'title') // Collection Model එකේ නම 'title' යැයි උපකල්පනය කරමි (නැත්නම් 'name' දාන්න)
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->label('Limit to Specific Collections'),
                ])->columns(2),

            // කොටස 3: කාලය සහ ප්‍රමාණය (Limits & Schedule)
            Forms\Components\Section::make('Limits & Schedule')
                ->icon('heroicon-m-clock')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('usage_limit')
                                ->label('Total Usage Limit')
                                ->numeric()
                                ->placeholder('Unlimited'),

                            Forms\Components\TextInput::make('usage_limit_per_user')
                                ->label('Usage Limit Per User')
                                ->numeric()
                                ->default(1)
                                ->helperText('How many times a single user can use this.'),
                        ]),

                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\DateTimePicker::make('starts_at')
                                ->label('Start Date')
                                ->default(now()),

                            Forms\Components\DateTimePicker::make('ends_at')
                                ->label('Expiry Date')
                                ->after('starts_at'),
                        ]),
                ]),

            // Sidebar Status Toggle
            Forms\Components\Section::make('Status')
                ->schema([
                    Forms\Components\Toggle::make('is_active')
                        ->label('Active Status')
                        ->default(true)
                        ->onColor('success')
                        ->offColor('danger'),
                ]),
        ];
    }
}
