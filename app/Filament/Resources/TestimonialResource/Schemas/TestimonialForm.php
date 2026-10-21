<?php

namespace App\Filament\Resources\TestimonialResource\Schemas;

use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Set;

class TestimonialForm
{
    public static function schema(): array
    {
        return [
            Group::make()
                ->schema([
                    Section::make('Review Details')
                        ->schema([
                            Select::make('user_id')
                                ->relationship('user', 'first_name')
                                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                                ->searchable(['first_name', 'last_name'])
                                ->preload()
                                ->placeholder('Select registered user (Optional / Guest)')
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set) {
                                    if ($state) {
                                        $user = User::find($state);
                                        if ($user) {
                                            $set('name', "{$user->first_name} {$user->last_name}");
                                        }
                                    }
                                }),

                            TextInput::make('name')
                                ->required()
                                ->maxLength(255)
                                ->helperText('Enter customer name manually if Guest, or auto-filled from User selection.'),

                            TextInput::make('location')
                                ->maxLength(255)
                                ->placeholder('e.g., Colombo, Sri Lanka'),

                            Radio::make('rating')
                                ->label('Rating')
                                ->options([
                                    5 => '⭐⭐⭐⭐⭐ 5',
                                    4 => '⭐⭐⭐⭐ 4',
                                    3 => '⭐⭐⭐ 3',
                                    2 => '⭐⭐ 2',
                                    1 => '⭐ 1',
                                ])
                                ->inline()
                                ->inlineLabel(false)
                                ->required()
                                ->default(5),

                            Textarea::make('quote')
                                ->required()
                                ->rows(5)
                                ->columnSpanFull(),
                        ])->columns(2),
                ])->columnSpan(['lg' => 2]),

            Group::make()
                ->schema([
                    Section::make('Status & Avatar')
                        ->schema([
                            FileUpload::make('avatar')
                                ->image()
                                ->directory('testimonials')
                                ->circleCropper(),

                            Toggle::make('is_active')
                                ->label('Approved / Active')
                                ->default(true),
                        ]),
                ])->columnSpan(['lg' => 1]),
        ];
    }
}
