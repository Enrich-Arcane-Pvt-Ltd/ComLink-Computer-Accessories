<?php

namespace App\Filament\Resources\GalleryItemResource\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class GalleryItemForm
{
    public static function schema(): array
    {
        return [
            Group::make()
                ->schema([
                    Section::make('Gallery Item Content')
                        ->schema([
                            TextInput::make('title')
                                ->label('Item Title (Optional)')
                                ->placeholder('e.g., Summer Collection Lookbook')
                                ->maxLength(255),

                            TextInput::make('external_link')
                                ->label('External / Instagram Link (Optional)')
                                ->placeholder('e.g., https://instagram.com/p/xxx')
                                ->url() // නිවැරදි URL එකක් විය යුතුය
                                ->validationAttribute('External Link')
                                ->maxLength(255),
                        ])->columns(1),
                ])->columnSpan(['lg' => 2]),

            Group::make()
                ->schema([
                    Section::make('Image & Settings')
                        ->schema([
                            FileUpload::make('image')
                                ->image()
                                ->directory('gallery')
                                ->required() // අනිවාර්යයි
                                ->maxSize(2048) // 2MB උපරිම සයිස් එක (User-friendly Validation)
                                ->imageEditor() // Admin ට කැමති විදිහට crop කරන්න පුළුවන්
                                ->helperText('Upload a high-quality image. Max 2MB.'),

                            Toggle::make('is_visible')
                                ->label('Visible in Gallery')
                                ->default(true),

                            Toggle::make('is_featured_home')
                                ->label('Show on Home Page Gallery') // ඔයා ඉල්ලපු Home page එකේ පෙන්වන ටික් එක
                                ->default(false),

                            TextInput::make('sort_order')
                                ->numeric()
                                ->default(0)
                                ->helperText('Lower numbers appear first.'),
                        ]),
                ])->columnSpan(['lg' => 1]),
        ];
    }
}
