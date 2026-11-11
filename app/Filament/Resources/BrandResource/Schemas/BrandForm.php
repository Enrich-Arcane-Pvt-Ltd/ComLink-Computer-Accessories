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
                        ->disk('public')
                        ->visibility('public')
                        ->directory('brands')
                        ->imageEditor()
                        ->maxSize(1024) // Maximum 1 MB (Safe for standard PHP 2MB upload limits)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
                        ->rules(['image', 'max:1024'])
                        ->validationMessages([
                            'max' => 'The brand image size must not exceed 1MB. Please compress or resize your image before uploading.',
                            'uploaded' => 'The brand image failed to upload. The file size exceeds the allowed limit (maximum 1MB). Please upload a smaller or compressed image.',
                            'image' => 'The uploaded file must be a valid image.',
                            'mimes' => 'The brand image must be a JPG, PNG, WebP, or SVG format.',
                        ])
                        ->helperText('Upload a clean logo for this brand. Maximum file size: 1MB (JPG, PNG, WebP, SVG). Please use compressed web-ready images.')
                        ->columnSpanFull(),

                    Forms\Components\Toggle::make('is_visible')
                        ->label('Visible on Storefront')
                        ->default(true)
                        ->helperText('Enable to display this brand across our web storefront.'),
                ])->columns(2),
        ];
    }
}
