<?php

namespace App\Filament\Resources\UserResource\Schemas;

use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        // Left Column
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Section::make('Personal Information')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('first_name')
                                                    ->required()
                                                    ->maxLength(255),

                                                Forms\Components\TextInput::make('last_name')
                                                    ->required()
                                                    ->maxLength(255),
                                            ]),

                                        Forms\Components\TextInput::make('email')
                                            ->email()
                                            ->required()
                                            ->unique(ignoreRecord: true),

                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('phone')
                                                    ->label('Primary Phone')
                                                    ->tel(),

                                                // අලුත් Secondary Phone එක
                                                Forms\Components\TextInput::make('secondary_phone')
                                                    ->label('Secondary Phone')
                                                    ->tel(),
                                            ]),

                                        // Password Section with Confirmation & Icon
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('password')
                                                    ->password()
                                                    ->revealable() // ඇහැ Icon එක (Show Password)
                                                    ->confirmed() // password_confirmation field එක check කරනවා
                                                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                                                    ->dehydrated(fn($state) => filled($state))
                                                    ->required(fn(string $context, $get) => $context === 'create' && !$get('is_guest'))
                                                    ->rule(Password::default()),

                                                Forms\Components\TextInput::make('password_confirmation')
                                                    ->label('Confirm Password')
                                                    ->password()
                                                    ->revealable() // මෙතනත් Icon එක දාමු
                                                    ->required(fn(string $context, $get) => $context === 'create' && filled($get('password')))
                                                    ->visible(fn($context, $get) => $context === 'create' || filled($get('password'))),
                                            ]),
                                    ]),
                            ])
                            ->columnSpan(2),

                        // Right Column (Avatar & Settings) - කලින් තිබ්බ විදියමයි
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Section::make('Avatar')
                                    ->schema([
                                        Forms\Components\FileUpload::make('avatar_url')
                                            ->hiddenLabel()
                                            ->avatar()
                                            ->imageEditor()
                                            ->directory('avatars')
                                            ->alignCenter()
                                            ->columnSpanFull(),
                                    ]),

                                Forms\Components\Section::make('Access & Status')
                                    ->schema([
                                        Forms\Components\Select::make('role')
                                            ->options(['admin' => 'Admin', 'user' => 'Customer'])
                                            ->default('user')
                                            ->required()
                                            ->native(false),

                                        Forms\Components\Select::make('status')
                                            ->options(['active' => 'Active', 'blocked' => 'Blocked'])
                                            ->default('active')
                                            ->required()
                                            ->native(false),

                                        Forms\Components\Toggle::make('is_guest')
                                            ->label('Is Guest User')
                                            ->reactive(),
                                    ]),
                            ])
                            ->columnSpan(1),
                    ]),
            ]);
    }
}
