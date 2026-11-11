<?php

namespace App\Filament\Resources\SubCategoryResource\Schemas;

use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Set;
use Illuminate\Support\Str;

class SubCategoryForm
{
    public static function schema(): array
    {
        return [
            Forms\Components\Section::make('Sub Category Details')
                ->description('Create or edit subcategories linked to a parent category.')
                ->schema([
                    Forms\Components\Select::make('parent_id')
                        ->label('Select the Category')
                        ->relationship('parent', 'name', modifyQueryUsing: fn ($query) => $query->whereNull('parent_id'))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->placeholder('Choose a parent category')
                        ->helperText('Select the main category this subcategory belongs to.'),

                    Forms\Components\TextInput::make('name')
                        ->label('Sub Category Title')
                        ->required()
                        ->placeholder('e.g. Processors, Motherboards, Keyboards')
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
                        ->label('Sub Category Image')
                        ->image()
                        ->imageEditor()
                        ->directory('categories')
                        ->maxSize(1024) // Maximum 1 MB (Safe for standard PHP 2MB upload limits)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
                        ->rules(['image', 'max:1024'])
                        ->validationMessages([
                            'max' => 'The subcategory image size must not exceed 1MB. Please compress or resize your image before uploading.',
                            'uploaded' => 'The subcategory image failed to upload. The file size exceeds the allowed limit (maximum 1MB). Please upload a smaller or compressed image.',
                            'image' => 'The uploaded file must be a valid image.',
                            'mimes' => 'The subcategory image must be a JPG, PNG, WebP, or SVG format.',
                        ])
                        ->helperText('Upload an image or thumbnail for this subcategory. Maximum file size: 1MB (JPG, PNG, WebP, SVG). Please use compressed web-ready images.')
                        ->columnSpanFull(),

                    Forms\Components\Toggle::make('is_visible')
                        ->label('Visible on Storefront')
                        ->default(true)
                        ->helperText('Control whether this subcategory displays under its category on the website.'),
                ])->columns(2),
        ];
    }
}
