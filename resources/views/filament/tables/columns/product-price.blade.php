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

    if (! $hasVariants) {
        $orig = (float) $record->price;
        $sale = $record->sale_price !== null ? (float) $record->sale_price : null;
        $hasDiscount = $sale !== null && $sale < $orig;
        $discountPct = $hasDiscount && $orig > 0 ? round((($orig - $sale) / $orig) * 100) : 0;
        $displayLatest = $sale !== null ? $sale : $orig;
    }
@endphp

<div style="display: flex; flex-direction: column; min-width: 0; padding: 2px 0; gap: 4px;">
    @if($hasVariants)
        {{-- Variant Products: Per-Color Latest Price, Original Price, Discount Percentage --}}
        <div style="display: flex; flex-direction: column; gap: 5px;">
            @foreach($variants as $v)
                @php
                    $vPrice = (float) ($v->price ?? 0);
                    $vSale = $v->sale_price !== null ? (float) $v->sale_price : null;
                    $vHasDiscount = $vSale !== null && $vSale > 0 && $vSale < $vPrice;
                    $vDiscountPct = (int) ($v->discount_percentage ?? 0);
                    if ($vDiscountPct <= 0 && $vHasDiscount && $vPrice > 0) {
                        $vDiscountPct = (int) round((($vPrice - $vSale) / $vPrice) * 100);
                    }
                    $vDisplayLatest = $vSale !== null && $vSale > 0 ? $vSale : $vPrice;
                    $dotColor = $getColorHex($v->color_name);
                @endphp
                <div style="display: flex; align-items: center; gap: 6px; white-space: nowrap;">
                    <span style="display: inline-block; width: 8px; height: 8px; min-width: 8px; border-radius: 50%; background-color: {{ $dotColor }}; box-shadow: 0 0 0 1px rgba(255,255,255,0.25); flex-shrink: 0;" title="{{ $v->color_name }}"></span>
                    <span class="font-bold text-sm text-gray-900 dark:text-gray-100" style="font-size: 13px;">
                        {{ $currency }}{{ number_format($vDisplayLatest, 2) }}
                    </span>
                    @if($vHasDiscount)
                        <span class="inline-flex items-center gap-1.5 text-xs" style="font-size: 11px;">
                            <span class="line-through text-gray-400 dark:text-gray-500 text-[11px]" style="font-size: 11px;">
                                {{ $currency }}{{ number_format($vPrice, 2) }}
                            </span>
                            <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950 px-1 rounded" style="font-size: 10px;">
                                -{{ $vDiscountPct }}%
                            </span>
                        </span>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        {{-- Standard Single Product --}}
        <span class="font-bold text-sm text-gray-900 dark:text-gray-100" style="font-size: 13px;">
            {{ $currency }}{{ number_format($displayLatest, 2) }}
        </span>
        @if($hasDiscount)
            <div class="flex items-center gap-1.5 text-xs mt-0.5" style="font-size: 11px;">
                <span class="line-through text-gray-400 dark:text-gray-500 text-[11px]" style="font-size: 11px;">
                    {{ $currency }}{{ number_format($orig, 2) }}
                </span>
                <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950 px-1 rounded" style="font-size: 10px;">
                    -{{ $discountPct }}%
                </span>
            </div>
        @endif
    @endif
</div>
