<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\MaxWidth;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Disable infolist to render the full ProductForm schema in read-only mode,
     * ensuring the view page layout exactly mirrors the edit option view.
     */
    public function hasInfolist(): bool
    {
        return false;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record->hasColorVariants()) {
            $data['has_color_variants'] = true;
            $data['color_variants'] = $this->record->variants->map(function ($variant) {
                return [
                    'id'                  => $variant->id,
                    'color_name'          => $variant->color_name,
                    'variant_name'        => $variant->variant_name ?? "{$this->record->name} - {$variant->color_name}",
                    'image'               => $variant->image,
                    'condition'           => $variant->condition ?? 'Brand New',
                    'inventory_stock'     => $variant->inventory_stock ?? 0,
                    'show_quantity'       => $variant->show_quantity ?? $variant->stock ?? 0,
                    'grn_price'           => $variant->grn_price,
                    'price'               => $variant->price,
                    'discount_percentage' => $variant->discount_percentage ?? 0,
                    'sale_price'          => $variant->sale_price,
                ];
            })->toArray();
        } else {
            $data['has_color_variants'] = false;
        }

        // Normalize main_specifications so both slug keys and title keys exist in form data
        if (! empty($this->record->main_specifications) && is_array($this->record->main_specifications)) {
            $normalizedMain = $data['main_specifications'] ?? [];
            foreach ($this->record->main_specifications as $k => $v) {
                if ($v === null || trim((string) $v) === '') {
                    continue;
                }
                $slug = \Illuminate\Support\Str::slug($k, '_');
                $normalizedMain[$slug] = $v;
                $normalizedMain[$k] = $v;
            }
            $data['main_specifications'] = $normalizedMain;
        }

        // Normalize technical_specifications so flat slug keys exist
        if (! empty($this->record->technical_specifications) && is_array($this->record->technical_specifications)) {
            $normalizedTech = $data['technical_specifications'] ?? [];
            $flattenTech = function ($arr) use (&$flattenTech, &$normalizedTech) {
                foreach ($arr as $k => $v) {
                    if (is_array($v)) {
                        $flattenTech($v);
                    } elseif ($v !== null && trim((string) $v) !== '') {
                        $slug = \Illuminate\Support\Str::slug($k, '_');
                        $normalizedTech[$slug] = $v;
                        $normalizedTech[$k] = $v;
                    }
                }
            };
            $flattenTech($this->record->technical_specifications);
            $data['technical_specifications'] = array_merge($data['technical_specifications'] ?? [], $normalizedTech);
        }

        return $data;
    }
}

