<?php

namespace App\Filament\Resources\CategoryResource\Schemas;

use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Set;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class CategoryForm
{
    public static function schema(): array
    {
        return [
            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make('Category Details')
                        ->schema([
                            // Parent Category Select
                            Forms\Components\Select::make('parent_id')
                                ->label('Parent Category')
                                ->relationship(
                                    'parent',
                                    'name',
                                    modifyQueryUsing: fn(Builder $query, ?Category $record) =>
                                    $record ? $query->where('id', '!=', $record->id)->whereNull('parent_id') : $query->whereNull('parent_id')
                                )
                                ->searchable()
                                ->preload()
                                ->placeholder('Leave empty for Main Category')
                                ->helperText('Select a parent to make this a Subcategory.'),

                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state))),

                            Forms\Components\TextInput::make('slug')
                                ->required()
                                ->maxLength(255)
                                ->unique(Category::class, 'slug', ignoreRecord: true)
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
                                ->directory('categories')
                                ->imageEditor(),

                            Forms\Components\Toggle::make('is_visible')
                                ->label('Visible to customers')
                                ->default(true),

                            Forms\Components\TextInput::make('sort_order')
                                ->numeric()
                                ->default(0)
                                ->helperText('Higher numbers appear first.'),
                        ]),
                ])->columnSpan(['lg' => 1]),
        ];
    }
}
