<?php

namespace App\Filament\Resources\BrandResource\Schemas;

use App\Models\Brand;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Illuminate\Support\Str;

class BrandForm
{
    public static function schema(): array
    {
        return [
            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make('Brand Information')
                        ->description('Basic details about the brand')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state))),

                            Forms\Components\TextInput::make('slug')
                                ->required()
                                ->maxLength(255)
                                ->unique(Brand::class, 'slug', ignoreRecord: true)
                                ->disabled()
                                ->dehydrated(),

                            Forms\Components\TextInput::make('url')
                                ->label('Website URL')
                                ->url()
                                ->suffixIcon('heroicon-m-globe-alt')
                                ->columnSpanFull(),

                            Forms\Components\MarkdownEditor::make('description')
                                ->columnSpanFull(),
                        ])->columns(2),
                ])->columnSpan(['lg' => 2]),

            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make('Visibility & Branding')
                        ->schema([
                            Forms\Components\FileUpload::make('logo')
                                ->image()
                                ->directory('brands')
                                ->imageEditor()
                                ->imageEditorAspectRatios([
                                    null,
                                    '16:9',
                                    '4:3',
                                    '1:1',
                                ]),

                            Forms\Components\ColorPicker::make('primary_hex')
                                ->label('Brand Theme Color'),

                            Forms\Components\Toggle::make('is_visible')
                                ->label('Visible to customers')
                                ->default(true)
                                ->helperText('Hide this brand from the store without deleting it.'),

                            Forms\Components\TextInput::make('sort_order')
                                ->numeric()
                                ->default(0)
                                ->helperText('Higher numbers appear first.'),
                        ]),
                ])->columnSpan(['lg' => 1]),
        ];
    }
}
