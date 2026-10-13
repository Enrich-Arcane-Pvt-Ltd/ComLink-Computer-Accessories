<?php

namespace App\Filament\Resources\PostResource;

use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\Pages\ViewPost;
use App\Filament\Resources\PostResource\RelationManagers\BlogCommentsRelationManager;
use App\Filament\Resources\PostResource\Schemas\PostForm;
use App\Filament\Resources\PostResource\Tables\PostTable;
use App\Models\Post;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?string $pluralLabel = 'Blogs';

    public static function form(Form $form): Form
    {
        return $form->schema(PostForm::schema())->columns(3);
    }

    public static function table(Table $table): Table
    {
        return PostTable::table($table);
    }

    public static function getRelations(): array
    {
        return [
            BlogCommentsRelationManager::class, // ✅ Admin panel comment approve/reject
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'view'   => ViewPost::route('/{record}'),
            'edit'   => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
