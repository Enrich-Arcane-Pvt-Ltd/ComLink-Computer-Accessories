@php
    $record = $getRecord();
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

    $stockCount = (int) $record->inventory_full_count;
    $soldCount = (int) $record->sold_count;
    $availableCount = max(0, $stockCount - $soldCount);

    $isOutOfStock = $availableCount <= 0;
    $isLowStock = $availableCount > 0 && $availableCount < 5;
    $availColor = $isOutOfStock ? '#ef4444' : ($isLowStock ? '#f59e0b' : '#10b981');
    $availBg = $isOutOfStock ? 'rgba(239, 68, 68, 0.12)' : ($isLowStock ? 'rgba(245, 158, 11, 0.12)' : 'rgba(16, 185, 129, 0.12)');
    $availBorder = $isOutOfStock ? 'rgba(239, 68, 68, 0.25)' : ($isLowStock ? 'rgba(245, 158, 11, 0.25)' : 'rgba(16, 185, 129, 0.25)');
@endphp

<div style="display: flex; flex-direction: column; min-width: 0; padding: 2px 0; gap: 4px;">
    @if($hasVariants)
        {{-- Variant Products: Per-Color Stock, Sold, Available --}}
        <div style="display: flex; flex-direction: column; gap: 5px;">
            @foreach($variants as $v)
                @php
                    $vStock = (int) ($v->inventory_stock ?? $v->stock ?? 0);
                    $vSold = (int) \App\Models\OrderItem::where('variant_id', $v->id)
                        ->whereHas('order', fn ($q) => $q->where('payment_status', 'Paid'))
                        ->sum('quantity');
                    $vAvail = max(0, $vStock - $vSold);

                    $vIsOut = $vAvail <= 0;
                    $vIsLow = $vAvail > 0 && $vAvail < 5;
                    $vStatusColor = $vIsOut ? '#ef4444' : ($vIsLow ? '#f59e0b' : '#10b981');
                    $dotColor = $getColorHex($v->color_name);
                @endphp
                <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; line-height: 1.35; white-space: nowrap;">
                    <span style="display: inline-block; width: 8px; height: 8px; min-width: 8px; border-radius: 50%; background-color: {{ $dotColor }}; box-shadow: 0 0 0 1px rgba(255,255,255,0.25); flex-shrink: 0;" title="{{ $v->color_name }}"></span>
                    <span style="font-weight: 600; color: #0284c7;" title="Stock Count">
                        Stock: {{ $vStock }}
                    </span>
                    <span style="opacity: 0.4;">•</span>
                    <span style="font-weight: 600; color: #a855f7;" title="Sold Count">
                        Sold: {{ $vSold }}
                    </span>
                    <span style="opacity: 0.4;">•</span>
                    <span style="display: inline-flex; align-items: center; gap: 3px; font-weight: 700; color: {{ $vStatusColor }};" title="Available">
                        <span style="display: inline-block; width: 5px; height: 5px; border-radius: 50%; background-color: {{ $vStatusColor }};"></span>
                        {{ $vAvail }} left
                    </span>
                </div>
            @endforeach
        </div>
    @else
        {{-- Standard Single Product --}}
        <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; line-height: 1.3;">
            <span style="display: inline-flex; align-items: center; gap: 3px; font-weight: 600; color: #0284c7;">
                <svg style="width: 12px; height: 12px; opacity: 0.8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                Stock: {{ $stockCount }}
            </span>
            <span style="opacity: 0.4;">•</span>
            <span style="display: inline-flex; align-items: center; gap: 3px; font-weight: 600; color: #a855f7;">
                <svg style="width: 12px; height: 12px; opacity: 0.8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                Sold: {{ $soldCount }}
            </span>
        </div>

        <div style="display: inline-flex; align-items: center; gap: 4px; margin-top: 2px;">
            <span style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; background-color: {{ $availBg }}; color: {{ $availColor }}; border: 1px solid {{ $availBorder }};">
                <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background-color: {{ $availColor }};"></span>
                @if($isOutOfStock)
                    Available: 0 (Sold Out)
                @elseif($isLowStock)
                    Available: {{ $availableCount }} left (Low)
                @else
                    Available: {{ $availableCount }} left
                @endif
            </span>
        </div>
    @endif
</div>
