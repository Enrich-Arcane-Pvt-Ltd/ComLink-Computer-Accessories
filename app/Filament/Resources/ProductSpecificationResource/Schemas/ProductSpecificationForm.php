<?php

namespace App\Filament\Resources\ProductSpecificationResource\Schemas;

use App\Models\Category;
use App\Models\ProductSpecification;
use App\Models\ProductSpecificationGroup;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;

class ProductSpecificationForm
{
    public static function loadExistingSpecifications(Set $set, ?int $categoryId, ?int $subCategoryId = null): void
    {
        if (! $categoryId) {
            $set('main_specs', []);
            $set('general_specs', []);
            $set('performance_specs', []);
            $set('compatibility_specs', []);

            return;
        }

        $group = null;

        if ($subCategoryId) {
            $group = ProductSpecificationGroup::where('category_id', $categoryId)
                ->where('sub_category_id', $subCategoryId)
                ->first();
        }

        if (! $group) {
            $group = ProductSpecificationGroup::where('category_id', $categoryId)
                ->whereNull('sub_category_id')
                ->first();
        }

        if ($group) {
            $set('main_specs', $group->main_specs ?? []);
            $set('general_specs', $group->general_specs ?? []);
            $set('performance_specs', $group->performance_specs ?? []);
            $set('compatibility_specs', $group->compatibility_specs ?? []);

            return;
        }

        $specQuery = ProductSpecification::where('category_id', $categoryId);
        if ($subCategoryId) {
            $specQuery->where(function ($q) use ($subCategoryId) {
                $q->where('sub_category_id', $subCategoryId)
                    ->orWhereNull('sub_category_id');
            });
        } else {
            $specQuery->whereNull('sub_category_id');
        }

        $specs = $specQuery->orderBy('sort_order')->orderBy('id')->get();

        if ($specs->isNotEmpty()) {
            $main = $specs->where('specification_type', 'main')->pluck('title')->unique()->values()->all();
            $gen = $specs->where('specification_type', 'technical')->where('sub_section', 'general')->pluck('title')->unique()->values()->all();
            $perf = $specs->where('specification_type', 'technical')->where('sub_section', 'performance')->pluck('title')->unique()->values()->all();
            $comp = $specs->where('specification_type', 'technical')->where('sub_section', 'compatibility')->pluck('title')->unique()->values()->all();

            $set('main_specs', $main);
            $set('general_specs', $gen);
            $set('performance_specs', $perf);
            $set('compatibility_specs', $comp);
        } else {
            $set('main_specs', []);
            $set('general_specs', []);
            $set('performance_specs', []);
            $set('compatibility_specs', []);
        }
    }

    public static function schema(): array
    {
        return [
            Forms\Components\Section::make('Category & Sub Category')
                ->description('Select the target category and subcategory. Existing specification titles will be loaded automatically.')
                ->schema([
                    Forms\Components\Select::make('category_id')
                        ->label('Category')
                        ->options(fn () => Category::whereNull('parent_id')->orderBy('sort_order')->pluck('name', 'id'))
                        ->required()
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function (Set $set, ?int $state) {
                            $set('sub_category_id', null);
                            static::loadExistingSpecifications($set, $state ? (int) $state : null, null);
                        })
                        ->columnSpan(1),

                    Forms\Components\Select::make('sub_category_id')
                        ->label('Sub Category')
                        ->placeholder(function (Get $get) {
                            $catId = $get('category_id');
                            if (! $catId) {
                                return 'Select a category first';
                            }
                            $hasSubs = Category::where('parent_id', $catId)->exists();

                            return $hasSubs ? 'Select Sub Category (or leave empty for whole category)' : 'No subcategories (Applies to whole category)';
                        })
                        ->options(function (Get $get) {
                            $catId = $get('category_id');
                            if (! $catId) {
                                return [];
                            }

                            return Category::where('parent_id', $catId)
                                ->orderBy('sort_order')
                                ->pluck('name', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get, ?int $state) {
                            $catId = $get('category_id');
                            static::loadExistingSpecifications($set, $catId ? (int) $catId : null, $state ? (int) $state : null);
                        })
                        ->columnSpan(1),
                ])->columns(2),

            Forms\Components\Section::make('Main Specification Titles')
                ->description('Specify the Main Specification attributes (e.g. Display size, Graphic cards, Processor, Memory, Storage, Type...)')
                ->schema([
                    Forms\Components\TagsInput::make('main_specs')
                        ->label('Main Specification Titles')
                        ->placeholder('Type attribute and press Enter (e.g. Brand, Display size, Processor...)')
                        ->helperText('Type title and press Enter or comma. Drag tags to change display order. These attributes determine order on product forms and shop filters.')
                        ->reorderable()
                        ->nestedRecursiveRules([
                            'min:1',
                            'max:255',
                        ])
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Technical Specification Titles')
                ->description('Define technical specification attributes grouped by General, Performance, and Compatibility.')
                ->schema([
                    Forms\Components\TagsInput::make('general_specs')
                        ->label('General Specification Titles')
                        ->placeholder('Type title and press Enter (e.g. Model / Series, Category Segment, Condition...)')
                        ->helperText('Attributes under Technical Specifications > General Specifications. Drag tags to change display order.')
                        ->reorderable()
                        ->columnSpanFull(),

                    Forms\Components\TagsInput::make('performance_specs')
                        ->label('Technical Performance Titles')
                        ->placeholder('Type title and press Enter (e.g. Interface / Connection, Architecture, Thermal Efficiency...)')
                        ->helperText('Attributes under Technical Specifications > Technical Performance. Drag tags to change display order.')
                        ->reorderable()
                        ->columnSpanFull(),

                    Forms\Components\TagsInput::make('compatibility_specs')
                        ->label('Compatibility & Standards Titles')
                        ->placeholder('Type title and press Enter (e.g. Operating Systems, Certification, Dimensions & Weight...)')
                        ->helperText('Attributes under Technical Specifications > Compatibility & Standards. Drag tags to change display order.')
                        ->reorderable()
                        ->columnSpanFull(),
                ]),
        ];
    }
}
