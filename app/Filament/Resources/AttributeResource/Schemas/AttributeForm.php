<?php

namespace App\Filament\Resources\AttributeResource\Schemas;

use Filament\Forms;
use Filament\Forms\Get;

class AttributeForm
{
    public static function schema(): array
    {
        return [
            Forms\Components\Section::make('Attribute Details')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Attribute Name (e.g. Color, Size)')
                        ->required(),

                    Forms\Components\Select::make('type')
                        ->options([
                            'text' => 'Text / Button (e.g. Size: S, M, L)',
                            'color' => 'Color Swatch (e.g. Red, Blue)',
                            'select' => 'Dropdown List',
                        ])
                        ->default('text')
                        ->required()
                        ->live(), // Type එක මාරු කරනකොට යට Form එක වෙනස් වෙන්න
                ])->columns(2),

            Forms\Components\Section::make('Attribute Values')
                ->schema([
                    Forms\Components\Repeater::make('values')
                        ->relationship() // AttributeValue Model එකට Auto Save වෙනවා
                        ->schema([
                            Forms\Components\TextInput::make('value')
                                ->label('Value (e.g. Red or XL)')
                                ->required(),

                            // Type එක 'color' නම් විතරක් පාට තෝරන්න දෙනවා
                            Forms\Components\ColorPicker::make('hex_color')
                                ->label('Color Picker')
                                ->visible(fn (Get $get) => $get('../../type') === 'color'),
                        ])
                        ->grid(2) // තීරු දෙකකට පෙන්නන්න
                        ->defaultItems(1)
                        ->addActionLabel('Add New Value'),
                ]),
        ];
    }
}
