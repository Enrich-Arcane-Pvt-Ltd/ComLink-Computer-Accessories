<?php

namespace App\Filament\Resources\SupplierResource\Schemas;

use Filament\Forms;

class SupplierForm
{
    public static function schema(): array
    {
        return [
            Forms\Components\Section::make('Supplier Information')
                ->description('Provide primary supplier credentials and contact details.')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Supplier Name')
                        ->required()
                        ->placeholder('e.g. TechZone Distributors (Pvt) Ltd')
                        ->maxLength(255)
                        ->columnSpan(2),

                    Forms\Components\TextInput::make('contact_no')
                        ->label('Contact No')
                        ->tel()
                        ->placeholder('e.g. +94 11 234 5678')
                        ->maxLength(50)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->placeholder('e.g. supplies@techzone.lk')
                        ->maxLength(255)
                        ->columnSpan(1),

                    Forms\Components\Textarea::make('address')
                        ->label('Address')
                        ->rows(3)
                        ->placeholder('e.g. No. 45, Galle Road, Colombo 03, Sri Lanka')
                        ->columnSpanFull(),
                ])->columns(2),
        ];
    }
}
