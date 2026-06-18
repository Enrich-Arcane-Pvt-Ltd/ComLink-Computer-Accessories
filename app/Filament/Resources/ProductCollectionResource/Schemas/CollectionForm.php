<?php

namespace App\Filament\Resources\ProductCollectionResource\Schemas;

use App\Models\ProductCollection;
use Filament\Forms;
use Filament\Forms\Set;
use Illuminate\Support\Str;

class CollectionForm
{
    public static function schema(): array
    {
        return [
            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make('Collection Details')
                        ->schema([
                            Forms\Components\TextInput::make('title')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state))),

                            Forms\Components\TextInput::make('slug')
                                ->required()
                                ->unique(ProductCollection::class, 'slug', ignoreRecord: true)
                                ->disabled()
                                ->dehydrated(),

                            Forms\Components\MarkdownEditor::make('description')
                                ->columnSpanFull(),
                        ])->columns(2),
                ])->columnSpan(['lg' => 2]),

            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make('Visuals & Status')
                        ->schema([
                            Forms\Components\FileUpload::make('image')
                                ->image()
                                ->directory('collections')
                                ->imageEditor(),

                            Forms\Components\Toggle::make('is_visible')
                                ->label('Visible to customers')
                                ->default(true),

                            Forms\Components\Toggle::make('is_featured')
                                ->label('Featured Collection')
                                ->helperText('Show on home page')
                                ->default(false),
                        ]),
                ])->columnSpan(['lg' => 1]),
        ];
    }
}
