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
                        ->directory('categories')
                        ->imageEditor()
                        ->columnSpanFull()
                        ->helperText('Upload an icon or banner image representing this category.'),

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
