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

    $showQty = $record->show_quantity ?? $record->quantity ?? 0;
    $availableQty = $record->quantity ?? 0;
    
    // Status color for remaining stock after payments
    $isOutOfStock = $availableQty <= 0;
    $isLowStock = $availableQty > 0 && $availableQty < 5;
@endphp

<div style="display: flex; flex-direction: column; min-width: 0; padding: 2px 0; gap: 4px;">
    @if($hasVariants)
        {{-- Variant Products: Per-Color Web Show & Available --}}
        <div style="display: flex; flex-direction: column; gap: 5px;">
            @foreach($variants as $v)
                @php
                    $vShow = (int) ($v->show_quantity ?? $v->stock ?? 0);
                    $vAvail = (int) ($v->stock ?? $v->show_quantity ?? 0);
                    $vIsOut = $vAvail <= 0;
                    $vIsLow = $vAvail > 0 && $vAvail < 5;
                    $vColor = $vIsOut ? '#ef4444' : ($vIsLow ? '#f59e0b' : '#10b981');
                    $dotColor = $getColorHex($v->color_name);
                @endphp
                <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; line-height: 1.35; white-space: nowrap;">
                    <span style="display: inline-block; width: 8px; height: 8px; min-width: 8px; border-radius: 50%; background-color: {{ $dotColor }}; box-shadow: 0 0 0 1px rgba(255,255,255,0.25); flex-shrink: 0;" title="{{ $v->color_name }}"></span>
                    <span 
                        style="display: inline-flex; align-items: center; justify-content: center; padding: 1px 7px; border-radius: 9999px; font-size: 11px; font-weight: 700; background-color: rgba(241, 245, 249, 0.1); color: inherit; border: 1px solid rgba(203, 213, 225, 0.25);" 
                        title="Web Show Count for {{ $v->color_name }}"
                    >
                        Show: {{ $vShow }}
                    </span>
                    <span style="display: inline-flex; align-items: center; gap: 3px; font-size: 10px; font-weight: 600; color: {{ $vColor }};">
                        <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background-color: {{ $vColor }};"></span>
                        {{ $vAvail }} left
                    </span>
                </div>
            @endforeach
        </div>
    @else
        {{-- Standard Single Product --}}
        <div style="display: inline-flex; align-items: center; gap: 5px;">
            <span 
                style="display: inline-flex; align-items: center; justify-content: center; padding: 2px 8px; border-radius: 9999px; font-size: 12px; font-weight: 700; background-color: #f1f5f9; color: #1e293b; border: 1px solid #cbd5e1; cursor: pointer;" 
                class="dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700 hover:border-cyan-500 hover:text-cyan-400 transition" 
                title="Click to adjust web show count"
            >
                <svg style="width: 12px; height: 12px; margin-right: 4px; opacity: 0.7;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                Show: {{ $showQty }}
                <svg style="width: 10px; height: 10px; margin-left: 4px; opacity: 0.6;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
            </span>
        </div>

        <div style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px;">
            @if($isOutOfStock)
                <span style="display: inline-flex; align-items: center; gap: 3px; font-weight: 600; color: #ef4444;">
                    <svg style="width: 11px; height: 11px;" fill="currentColor" viewBox="0 0 20 20">
                        <circle cx="10" cy="10" r="8" fill="#ef4444" />
                    </svg>
                    Available: 0 (Sold Out)
                </span>
            @elseif($isLowStock)
                <span style="display: inline-flex; align-items: center; gap: 3px; font-weight: 600; color: #f59e0b;">
                    <svg style="width: 11px; height: 11px;" fill="currentColor" viewBox="0 0 20 20">
                        <circle cx="10" cy="10" r="8" fill="#f59e0b" />
                    </svg>
                    Available: {{ $availableQty }} left (Low)
                </span>
            @else
                <span style="display: inline-flex; align-items: center; gap: 3px; font-weight: 600; color: #10b981;">
                    <svg style="width: 11px; height: 11px;" fill="currentColor" viewBox="0 0 20 20">
                        <circle cx="10" cy="10" r="8" fill="#10b981" />
                    </svg>
                    Available: {{ $availableQty }} left
                </span>
            @endif
        </div>
    @endif
</div>
