<?php

namespace App\Filament\Resources\BrandResource\Schemas;

use App\Models\Brand;
use Filament\Forms;
use Filament\Forms\Set;
use Illuminate\Support\Str;

class BrandForm
{
    public static function schema(): array
    {
        return [
            Forms\Components\Section::make('Brand Information')
                ->description('Create or edit computer hardware brand.')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Brand Title')
                        ->required()
                        ->placeholder('e.g. ASUS, MSI, Corsair, AMD, Intel')
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),

                    Forms\Components\TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(Brand::class, 'slug', ignoreRecord: true)
                        ->disabled()
                        ->dehydrated(),

                    Forms\Components\FileUpload::make('logo')
                        ->label('Brand Image')
                        ->image()
                        ->directory('brands')
                        ->imageEditor()
                        ->columnSpanFull()
                        ->helperText('Upload a clean PNG or SVG logo for this brand.'),

                    Forms\Components\Toggle::make('is_visible')
                        ->label('Visible on Storefront')
                        ->default(true)
                        ->helperText('Enable to display this brand across our web storefront.'),
                ])->columns(2),
        ];
    }
}
