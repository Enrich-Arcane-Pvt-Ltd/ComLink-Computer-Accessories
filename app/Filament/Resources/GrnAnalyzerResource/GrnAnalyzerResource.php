<?php

namespace App\Filament\Resources\GrnAnalyzerResource;

use App\Filament\Resources\GrnAnalyzerResource\Pages;
use App\Filament\Resources\GrnAnalyzerResource\Tables\GrnAnalyzerTable;
use App\Models\GrnReport;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class GrnAnalyzerResource extends Resource
{
    protected static ?string $model = GrnReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationGroup = 'Inventory Management';

    protected static ?string $navigationLabel = 'GRN Analyzer';

    protected static ?string $modelLabel = 'GRN Analysis Report';

    protected static ?string $pluralModelLabel = 'GRN Analyzer';

    protected static ?string $slug = 'grn-analyzer';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form;
    }

    public static function table(Table $table): Table
    {
        return GrnAnalyzerTable::table($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGrnAnalyzers::route('/'),
        ];
    }
}
