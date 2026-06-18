<?php

namespace App\Filament\Resources\ProductResource\Schemas;

use App\Models\Product;
use App\Models\SystemSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Str;

class ProductForm
{
    public static function schema(): array
    {
        // Currency Symbol එක ගැනීම (Prefix සඳහා)
        $currency = SystemSetting::first()->currency_symbol ?? 'LKR';

        return [
            Forms\Components\Tabs::make('Product Details')
                ->tabs([
                    // 1. මූලික විස්තර (General Info)
                    Forms\Components\Tabs\Tab::make('General')
                        ->icon('heroicon-m-information-circle')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state))),

                            Forms\Components\TextInput::make('slug')
                                ->required()
                                ->unique(Product::class, 'slug', ignoreRecord: true)
                                ->readOnly(), // Auto-generated නිසා Read Only

                            Forms\Components\Select::make('brand_id')
                                ->relationship('brand', 'name')
                                ->searchable()
                                ->preload()
                                ->label('Brand'),

                            Forms\Components\MarkdownEditor::make('description')
                                ->columnSpanFull(),
                        ]),

                    // 2. පින්තූර (Images)
                    Forms\Components\Tabs\Tab::make('Images')
                        ->icon('heroicon-m-photo')
                        ->schema([
                            Forms\Components\FileUpload::make('images')
                                ->label('Product Gallery')
                                ->image()
                                ->multiple() // පින්තූර ගොඩක් දාන්න පුළුවන්
                                ->reorderable() // අනුපිළිවෙල මාරු කරන්න පුළුවන්
                                ->directory('products')
                                ->imageEditor()
                                // ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg'])
                                ->required()
                                ->columnSpanFull(),
                        ]),

                    // 3. මිල සහ ස්ටොක් (Pricing & Inventory)
                    Forms\Components\Tabs\Tab::make('Pricing & Inventory')
                        ->icon('heroicon-m-currency-dollar')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\Select::make('type')
                                        ->options([
                                            'simple' => 'Simple Product',
                                            'variable' => 'Variable Product (Color/Size)',
                                        ])
                                        ->default('simple')
                                        ->required()
                                        ->live() // Type එක මාරු වෙනකොට Form එක වෙනස් වෙන්න
                                        ->columnSpanFull(),

                                    // Simple Product එකක් නම් විතරක් මේවා පෙන්වන්න
                                    Forms\Components\Group::make()
                                        ->schema([
                                            Forms\Components\TextInput::make('sku')
                                                ->label('SKU (Stock Keeping Unit)')
                                                ->unique(Product::class, 'sku', ignoreRecord: true),

                                            Forms\Components\TextInput::make('price')
                                                ->label('Regular Price')
                                                ->numeric()
                                                ->prefix($currency)
                                                ->required(),

                                            Forms\Components\TextInput::make('sale_price')
                                                ->label('Sale Price')
                                                ->numeric()
                                                ->prefix($currency)
                                                ->lte('price'), // Price එකට වඩා අඩු වෙන්න ඕන

                                            Forms\Components\TextInput::make('quantity')
                                                ->label('Stock Quantity')
                                                ->numeric()
                                                ->default(0)
                                                ->required(),
                                        ])
                                        ->visible(fn(Get $get) => $get('type') === 'simple')
                                        ->columns(2)
                                        ->columnSpanFull(),

                                    // පොදු දේවල් (Simple/Variable දෙකටම)
                                    Forms\Components\TextInput::make('cost_price')
                                        ->label('Cost Price (For Profit Calc)')
                                        ->numeric()
                                        ->prefix($currency),

                                    Forms\Components\TextInput::make('security_stock')
                                        ->label('Low Stock Alert Limit')
                                        ->numeric()
                                        ->default(10),
                                ]),
                        ]),

                    // 4. සංවිධානය (Organization)
                    Forms\Components\Tabs\Tab::make('Organization')
                        ->icon('heroicon-m-tag')
                        ->schema([
                            Forms\Components\CheckboxList::make('categories')
                                ->relationship('categories', 'name')
                                ->searchable()
                                ->columns(2),

                            Forms\Components\CheckboxList::make('collections')
                                ->relationship('collections', 'title')
                                ->searchable()
                                ->columns(2),
                        ]),

                    // 5. Variants (Variable Product නම් විතරයි)
                    Forms\Components\Tabs\Tab::make('Variations')
                        ->icon('heroicon-m-swatch')
                        ->visible(fn(Get $get) => $get('type') === 'variable')
                        ->schema([
                            Forms\Components\Repeater::make('variants')
                                ->relationship()
                                ->schema([
                                    Forms\Components\Group::make()
                                        ->schema([
                                            Forms\Components\FileUpload::make('image')
                                                ->label('Variant Image')
                                                ->image()
                                                ->directory('product-variants')
                                                ->nullable()
                                                ->columnSpanFull(),

                                            Forms\Components\Grid::make(3)
                                                ->schema([
                                                    Forms\Components\TextInput::make('sku')
                                                        ->label('SKU')
                                                        ->required(),

                                                    Forms\Components\TextInput::make('price')
                                                        ->label('Price')
                                                        ->numeric()
                                                        ->prefix($currency)
                                                        ->required(),

                                                    Forms\Components\TextInput::make('stock')
                                                        ->label('Stock')
                                                        ->numeric()
                                                        ->default(0)
                                                        ->required(),
                                                ]),

                                            // 👇 මේ කොටස තමයි අපි අලුතින් වෙනස් කළේ (KeyValue එක වෙනුවට Repeater)
                                            Forms\Components\Repeater::make('attributes')
                                                ->label('Variation Options')
                                                // මෙතන JSON එක Save වෙන්නේ Array එකක් විදියට: [{"name": "Color", "value": "Red"}]
                                                // කලින් තිබුනේ {"Color": "Red"} විදියට.
                                                // අලුත් ක්‍රමය වඩා හොඳයි මොකද අපිට Select කරන්න පුළුවන් නිසා.
                                                ->schema([
                                                    Forms\Components\Grid::make(2)
                                                        ->schema([
                                                            // 1. Attribute එක තෝරන්න (Ex: Color)
                                                            Forms\Components\Select::make('name')
                                                                ->label('Attribute')
                                                                ->options(\App\Models\Attribute::pluck('name', 'name')) // Database එකෙන් ගන්නවා
                                                                ->searchable()
                                                                ->required()
                                                                ->live(), // මේක මාරු කළාම යට එක වෙනස් වෙන්න ඕන

                                                            // 2. Value එක තෝරන්න (Ex: Red)
                                                            Forms\Components\Select::make('value')
                                                                ->label('Option')
                                                                ->required()
                                                                // උඩින් තෝරපු Attribute එකට අදාල Values විතරක් පෙන්වන්න
                                                                ->options(function (Get $get) {
                                                                    $attributeName = $get('name');
                                                                    if (!$attributeName) return [];

                                                                    // Attribute නමෙන් අදාල ID එක හොයාගෙන, ඒකේ Values ටික ගන්නවා
                                                                    $attribute = \App\Models\Attribute::where('name', $attributeName)->first();
                                                                    return $attribute ? $attribute->values()->pluck('value', 'value') : [];
                                                                }),
                                                        ]),
                                                ])
                                                ->disableItemMovement()
                                                ->disableLabel()
                                                ->addActionLabel('Add Option')
                                                ->grid(2),
                                        ]),
                                ])
                                ->itemLabel(
                                    fn(array $state): ?string =>
                                    isset($state['sku']) ? "Variant: {$state['sku']}" : 'New Variant'
                                )
                                ->collapsed()
                                ->cloneable()
                                ->grid(1),
                        ]),
                ])->columnSpanFull(),

            // Sidebar (දකුණු පැත්තේ කොටස)
            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make('Status')
                        ->schema([
                            Forms\Components\Toggle::make('is_visible')
                                ->label('Visible')
                                ->default(true),

                            Forms\Components\Toggle::make('is_featured')
                                ->label('Featured Product'),

                            Forms\Components\DatePicker::make('published_at')
                                ->label('Publish Date')
                                ->default(now()),
                        ]),
                ])->columnSpan(1), // මෙය Sidebar එකක් ලෙස තැබීමට Layout එක Columns 3ක් කළ යුතුයි
        ];
    }
}
