<?php

namespace App\Filament\Resources\NewsletterSubscriberResource;

use App\Filament\Resources\NewsletterSubscriberResource\Schemas\SubscriberInfolist;
use App\Filament\Resources\NewsletterSubscriberResource\Tables\SubscriberTable;
use App\Models\NewsletterSubscriber;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class NewsletterSubscriberResource extends Resource
{
    protected static ?string $model = NewsletterSubscriber::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?string $pluralLabel = 'Newsletter Subscribers';

    // Form එකක් අවශ්‍ය නැත (Edit/Create නැති නිසා)
    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return SubscriberTable::table($table);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return SubscriberInfolist::infolist($infolist);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageNewsletterSubscribers::route('/'),
        ];
    }
}
