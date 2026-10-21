<?php

namespace App\Filament\Resources\SlideResource\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class SlideForm
{
    public static function schema(): array
    {
        return [
            Group::make()
                ->schema([
                    Section::make('Slide Content')
                        ->schema([
                            TextInput::make('title')
                                ->required()
                                ->columnSpanFull()
                                ->maxLength(255)
                                ->placeholder('Enter catchy slide title...'),

                            TextInput::make('subtitle')
                                ->maxLength(255)
                                ->placeholder('Enter optional subtitle...'),

                            Select::make('location')
                                ->options([
                                    'hero' => 'Main Hero Slider',
                                    'cta_1' => 'Call to Action 1 (Middle)',
                                    'cta_2' => 'Call to Action 2 (Bottom)',
                                    'newsletter' => 'Newsletter Background',
                                ])
                                ->required()
                                ->native(false),

                            Textarea::make('description')
                                ->rows(3)
                                ->columnSpanFull()
                                ->placeholder('Provide a brief description for this slide...'),

                            TextInput::make('button_text')
                                ->placeholder('e.g., Shop Now')
                                ->maxLength(50),

                            TextInput::make('button_url')
                                ->placeholder('e.g., /products/t-shirts')
                                ->url()
                                ->maxLength(255),
                        ])->columns(2),
                ])->columnSpan(['lg' => 2]),

            Group::make()
                ->schema([
                    Section::make('Visuals & Visibility')
                        ->schema([
                            FileUpload::make('image')
                                ->image()
                                ->directory('slides')
                                ->required()
                                ->imageEditor()
                                ->maxSize(2048)
                                ->rules([
                                    'dimensions:width=1920,height=1080',
                                ])
                                ->imageEditorAspectRatios([
                                    '16:9',
                                ])
                                ->helperText('Image must be exactly 1920x1080 pixels and under 2MB.'),

                            TextInput::make('sort_order')
                                ->numeric()
                                ->integer()
                                ->minValue(0)
                                ->default(0),

                            Toggle::make('is_active')
                                ->label('Visible on Website')
                                ->default(true)
                                ->onColor('success')
                                ->offColor('danger'),
                        ]),
                ])->columnSpan(['lg' => 1]),
        ];
    }
}
