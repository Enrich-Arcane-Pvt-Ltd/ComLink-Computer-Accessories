<?php

namespace App\Filament\Resources\CategoryResource\Schemas;

use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Set;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function schema(): array
    {
        return [
            Forms\Components\Section::make('Category Information')
                ->description('Create or edit top-level hardware product categories.')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Category Title')
                        ->required()
                        ->placeholder('e.g. PC Components, Laptops, Peripherals')
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),

                    Forms\Components\TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(Category::class, 'slug', ignoreRecord: true)
                        ->disabled()
                        ->dehydrated(),

                    Forms\Components\FileUpload::make('image')
                        ->label('Category Image')
                        ->image()
                        ->imageEditor()
                        ->directory('categories')
                        ->maxSize(1024) // Maximum 1 MB (Safe for standard PHP 2MB upload limits)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
                        ->rules(['image', 'max:1024'])
                        ->validationMessages([
                            'max' => 'The category image size must not exceed 1MB. Please compress or resize your image before uploading.',
                            'uploaded' => 'The category image failed to upload. The file size exceeds the allowed limit (maximum 1MB). Please upload a smaller or compressed image.',
                            'image' => 'The uploaded file must be a valid image.',
                            'mimes' => 'The category image must be a JPG, PNG, WebP, or SVG format.',
                        ])
                        ->helperText('Upload an icon or banner image representing this category. Maximum file size: 1MB (JPG, PNG, WebP, SVG). Please use compressed web-ready images.')
                        ->columnSpanFull(),

                    Forms\Components\Toggle::make('is_visible')
                        ->label('Visible on Storefront')
                        ->default(true)
                        ->helperText('Enable to display this category on the public website.'),

                    Forms\Components\Hidden::make('parent_id')
                        ->default(null),
                ])->columns(2),
        ];
    }
}
