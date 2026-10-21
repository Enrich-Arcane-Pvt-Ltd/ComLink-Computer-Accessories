@php
    $record = $getRecord();
    $currency = config('app.currency_symbol', 'Rs. ');
    $variants = $record->variants ?? collect();
    $hasVariants = $record->hasColorVariants() && $variants->isNotEmpty();

    $getColorHex = function (?string $name): string {
        if (! $name) return '#94a3b8';
        $n = strtolower(trim($name));
        if (str_contains($n, 'orange')) return '#f97316';
        if (str_contains($n, 'yellow')) return '#eab308';
        if (str_contains($n, 'blue') || str_contains($n, 'cyan') || str_contains($n, 'navy')) return '#3b82f6';
        if (str_contains($n, 'red') || str_contains($n, 'crimson') || str_contains($n, 'ruby')) return '#ef4444';
        if (str_contains($n, 'green') || str_contains($n, 'emerald') || str_contains($n, 'lime')) return '#22c55e';
        if (str_contains($n, 'purple') || str_contains($n, 'violet')) return '#a855f7';
        if (str_contains($n, 'pink') || str_contains($n, 'rose')) return '#ec4899';
        if (str_contains($n, 'black') || str_contains($n, 'dark')) return '#27272a';
        if (str_contains($n, 'white')) return '#f4f4f5';
        if (str_contains($n, 'gray') || str_contains($n, 'grey')) return '#71717a';
        if (str_contains($n, 'silver')) return '#94a3b8';
        if (str_contains($n, 'gold')) return '#eab308';
        if (str_contains($n, 'brown')) return '#854d0e';
        
        $hash = abs(crc32($n));
        $hue = $hash % 360;
        return "hsl({$hue}, 75%, 50%)";
    };

    if ($record->product_code) {
        $parentPrice = \App\Models\GrnItem::where('product_code', $record->product_code)->latest('id')->value('price_per_unit')
            ?? $record->grn_price;
    } else {
        $parentPrice = $record->grn_price;
    }
    $parentPrice = (float) ($parentPrice ?? 0);
@endphp

<div style="display: flex; flex-direction: column; min-width: 0; padding: 2px 0; gap: 4px;">
    @if($hasVariants)
        {{-- Variant Products: Per-Color GRN Price --}}
        <div style="display: flex; flex-direction: column; gap: 5px;">
            @foreach($variants as $v)
                @php
                    $vPrice = $v->grn_price !== null ? (float) $v->grn_price : $parentPrice;
                    $dotColor = $getColorHex($v->color_name);
                @endphp
                <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; line-height: 1.35; white-space: nowrap;">
                    <span style="display: inline-block; width: 8px; height: 8px; min-width: 8px; border-radius: 50%; background-color: {{ $dotColor }}; box-shadow: 0 0 0 1px rgba(255,255,255,0.25); flex-shrink: 0;" title="{{ $v->color_name }}"></span>
                    <span style="display: inline-flex; align-items: center; padding: 1px 7px; border-radius: 6px; font-size: 11px; font-weight: 600; background-color: rgba(107, 114, 128, 0.15); color: #d1d5db; border: 1px solid rgba(156, 163, 175, 0.25);">
                        {{ $currency }}{{ number_format($vPrice, 2) }}
                    </span>
                </div>
            @endforeach
        </div>
    @else
        {{-- Standard Single Product --}}
        <span style="display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 6px; font-size: 12px; font-weight: 600; background-color: rgba(107, 114, 128, 0.15); color: #d1d5db; border: 1px solid rgba(156, 163, 175, 0.25); width: fit-content;">
            {{ $currency }}{{ number_format($parentPrice, 2) }}
        </span>
    @endif
</div>
