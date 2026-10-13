<?php

namespace App\Filament\Resources\ContactInquiryResource\Pages;

use App\Filament\Resources\ContactInquiryResource\ContactInquiryResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListContactInquiries extends ListRecords
{
    protected static string $resource = ContactInquiryResource::class;

    public function getTabs(): array
    {
        return [
            'pending' => Tab::make('Pending Inquiries')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'pending'))
                ->badge(\App\Models\ContactInquiry::where('status', 'pending')->count())
                ->badgeColor('warning'),

            'replied' => Tab::make('Replied')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'replied'))
                ->badge(\App\Models\ContactInquiry::where('status', 'replied')->count())
                ->badgeColor('success'),

            'spam' => Tab::make('Spam')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'spam')),

            'all' => Tab::make('All Inquiries'),
        ];
    }
}
