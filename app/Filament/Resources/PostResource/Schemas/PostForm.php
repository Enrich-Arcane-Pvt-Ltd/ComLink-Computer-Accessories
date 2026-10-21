<?php

namespace App\Filament\Resources\PostResource\Schemas;

use App\Models\Post;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Set;
use Illuminate\Support\Str;

class PostForm
{
    public static function schema(): array
    {
        // DB එකෙන් existing categories load කරනවා → Select options
        $existingCategories = Post::whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->mapWithKeys(fn ($c) => [$c => $c])
            ->toArray();

        // DB එකෙන් existing tags load කරනවා → TagsInput suggestions
        $existingTags = Post::whereNotNull('tags')
            ->pluck('tags')
            ->flatten()
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        return [
            Group::make()
                ->schema([
                    Section::make('Blog Content')
                        ->schema([
                            TextInput::make('title')
                                ->label('Post Title')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),

                            TextInput::make('slug')
                                ->required()
                                ->maxLength(255)
                                ->unique(Post::class, 'slug', ignoreRecord: true),

                            Textarea::make('excerpt')
                                ->label('Short Excerpt')
                                ->helperText('A short summary of the post shown on the listing page.')
                                ->maxLength(500)
                                ->rows(3)
                                ->columnSpanFull(),

                            RichEditor::make('content')
                                ->label('Full Article Body')
                                ->required()
                                ->columnSpanFull(),
                        ])->columns(2),
                ])->columnSpan(['lg' => 2]),

            Group::make()
                ->schema([
                    Section::make('Publishing & Metadata')
                        ->schema([
                            FileUpload::make('image')
                                ->image()
                                ->directory('blogs')
                                ->maxSize(2048)
                                ->required()
                                ->imageEditor()
                                ->helperText('Max file size: 2MB.'),

                            TextInput::make('author')
                                ->default('Admin')
                                ->required()
                                ->maxLength(100),

                            Textarea::make('author_intro')
                                ->label('About Author')
                                ->helperText('A short summary of author.')
                                ->maxLength(500)
                                ->rows(2)
                                ->columnSpanFull(),

                            // ✅ Select + creatable — existing categories show + නව ones type කරන්නත් පුළුවන්
                            Select::make('category')
                                ->options($existingCategories)
                                ->searchable()
                                ->createOptionForm([
                                    TextInput::make('category')
                                        ->label('New Category')
                                        ->required(),
                                ])
                                ->createOptionUsing(fn (array $data) => $data['category'])
                                ->placeholder('Select or type new category...')
                                ->required(),

                            // ✅ TagsInput with suggestions — existing tags suggest කරනවා
                            TagsInput::make('tags')
                                ->placeholder('Add tags...')
                                ->separator(',')
                                ->suggestions($existingTags),

                            Toggle::make('is_active')
                                ->label('Published Status')
                                ->default(true),
                        ]),
                ])->columnSpan(['lg' => 1]),
        ];
    }
}
