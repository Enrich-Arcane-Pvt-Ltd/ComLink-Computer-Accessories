<?php

namespace App\Filament\Resources\NavbarControllerResource\Tables;

use App\Models\Category;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;

class NavbarControllerTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Icon/Image')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder.png')),

                Tables\Columns\TextColumn::make('name')
                    ->label('Category in Navbar')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('children.name')
                    ->label('Subcategories (Navbar Dropdown)')
                    ->badge()
                    ->color('info')
                    ->separator(', ')
                    ->limitList(5)
                    ->expandableLimitedList(),

                Tables\Columns\ToggleColumn::make('is_visible')
                    ->label('Visible in Navbar')
                    ->onColor('success')
                    ->offColor('danger')
                    ->tooltip('Toggle whether this category appears in the storefront navbar'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Display Order')
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn ($state) => '#'.((int) $state + 1))
                    ->alignCenter()
                    ->sortable()
                    ->tooltip('Position in navbar (left-to-right). Use the ↑ / ↓ buttons to swap order.'),
            ])
            ->defaultSort('sort_order', 'asc')
            ->reorderable('sort_order')
            ->actions([
                Tables\Actions\Action::make('move_up')
                    ->label('Move Up')
                    ->icon('heroicon-m-arrow-up')
                    ->iconButton()
                    ->color('primary')
                    ->tooltip('Swap with category above')
                    ->disabled(fn (Category $record): bool => ! Category::whereNull('parent_id')
                        ->where('sort_order', '<', $record->sort_order)
                        ->exists()
                    )
                    ->action(function (Category $record) {
                        $previous = Category::whereNull('parent_id')
                            ->where('sort_order', '<', $record->sort_order)
                            ->orderBy('sort_order', 'desc')
                            ->first();

                        if ($previous) {
                            $prevOrder = $previous->sort_order;
                            $currentOrder = $record->sort_order;

                            if ($prevOrder === $currentOrder) {
                                $prevOrder = max(0, $currentOrder - 1);
                            }

                            $previous->sort_order = $currentOrder;
                            $record->sort_order = $prevOrder;

                            $previous->save();
                            $record->save();
                        }
                    }),

                Tables\Actions\Action::make('move_down')
                    ->label('Move Down')
                    ->icon('heroicon-m-arrow-down')
                    ->iconButton()
                    ->color('primary')
                    ->tooltip('Swap with category below')
                    ->disabled(fn (Category $record): bool => ! Category::whereNull('parent_id')
                        ->where('sort_order', '>', $record->sort_order)
                        ->exists()
                    )
                    ->action(function (Category $record) {
                        $next = Category::whereNull('parent_id')
                            ->where('sort_order', '>', $record->sort_order)
                            ->orderBy('sort_order', 'asc')
                            ->first();

                        if ($next) {
                            $nextOrder = $next->sort_order;
                            $currentOrder = $record->sort_order;

                            if ($nextOrder === $currentOrder) {
                                $nextOrder = $currentOrder + 1;
                            }

                            $next->sort_order = $currentOrder;
                            $record->sort_order = $nextOrder;

                            $next->save();
                            $record->save();
                        }
                    }),

                Tables\Actions\Action::make('manage_subcategories')
                    ->label('Manage Submenu Order')
                    ->icon('heroicon-m-queue-list')
                    ->color('info')
                    ->modalHeading(fn (Category $record): string => "Navbar Submenu Order: {$record->name}")
                    ->modalDescription('Drag items or use the ↑ / ↓ buttons to swap the display order of subcategories in the navbar dropdown.')
                    ->modalSubmitActionLabel('Save Order')
                    ->slideOver()
                    ->fillForm(function (Category $record): array {
                        $subs = $record->children()->orderBy('sort_order', 'asc')->get();

                        return [
                            'subcategories' => $subs->map(fn ($sub) => [
                                'id' => $sub->id,
                                'name' => $sub->name,
                                'is_visible' => (bool) $sub->is_visible,
                            ])->toArray(),
                        ];
                    })
                    ->form(function (Category $record) {
                        $subsCount = $record->children()->count();
                        if ($subsCount === 0) {
                            return [
                                Forms\Components\Placeholder::make('no_subs')
                                    ->content('No subcategories exist for this category yet. You can add them under Product Catalog > Sub Categories.'),
                            ];
                        }

                        return [
                            Forms\Components\Repeater::make('subcategories')
                                ->label('Subcategories')
                                ->schema([
                                    Forms\Components\Hidden::make('id'),
                                    Forms\Components\TextInput::make('name')
                                        ->label('Subcategory Name')
                                        ->disabled()
                                        ->columnSpan(1),
                                    Forms\Components\Toggle::make('is_visible')
                                        ->label('Visible in Navbar')
                                        ->inline(false)
                                        ->columnSpan(1),
                                ])
                                ->columns(2)
                                ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                                ->reorderable(true)
                                ->reorderableWithButtons(true)
                                ->reorderableWithDragAndDrop(true)
                                ->addable(false)
                                ->deletable(false)
                                ->collapsible(false),
                        ];
                    })
                    ->action(function (Category $record, array $data) {
                        $items = $data['subcategories'] ?? [];
                        foreach ($items as $index => $item) {
                            if (! empty($item['id'])) {
                                Category::where('id', $item['id'])->update([
                                    'sort_order' => $index,
                                    'is_visible' => (bool) ($item['is_visible'] ?? true),
                                ]);
                            }
                        }
                    }),

                Tables\Actions\EditAction::make()
                    ->slideOver()
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->label('Category Name')
                            ->required(),
                        Forms\Components\Toggle::make('is_visible')
                            ->label('Show in Navbar')
                            ->default(true),
                    ]),
            ])
            ->bulkActions([]);
    }
}
