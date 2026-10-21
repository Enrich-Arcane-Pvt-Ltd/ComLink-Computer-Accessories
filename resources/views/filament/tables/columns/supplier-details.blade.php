@php
    $record = $getRecord();
    $supplierName = $record->supplier?->name;
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

    // Inventory added date & live inventory stock from GRN inventory
    if ($record->product_code) {
        $latestGrn = \App\Models\GrnItem::where('product_code', $record->product_code)->latest('id')->first();
        $rawDate = $latestGrn?->created_at ?? $record->stock_added_date ?? $record->created_at;
        $invStock = (int) \App\Models\GrnItem::where('product_code', $record->product_code)->sum('product_quantity');
    } else {
        $rawDate = $record->stock_added_date ?? $record->created_at;
        $invStock = (int) ($record->inventory_stock ?? $record->quantity ?? 0);
    }
    $date = $rawDate ? \Carbon\Carbon::parse($rawDate)->format('M d, Y') : 'N/A';
    
    // Web Show Count currently allocated
    $webShow = (int) ($record->show_quantity ?? $record->quantity ?? 0);
    
    // Balance Inventory Stock remaining in warehouse
    $balanceStock = max(0, $invStock - $webShow);

    // Color logic: yellow normally, red when count becomes too low (<= 5 units)
    $isTooLow = $balanceStock <= 5;
    $balanceColor = $isTooLow ? '#ef4444' : '#eab308';
@endphp

<div style="display: flex; flex-direction: column; min-width: 0; padding: 2px 0; gap: 3px;">
    {{-- Supplier Name --}}
    <div style="display: flex; align-items: center; gap: 6px;">
        <svg style="width: 14px; height: 14px; color: #06b6d4; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
        <span style="font-weight: 700; font-size: 13px; color: inherit; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 260px;" title="{{ $supplierName ?? 'Direct Stock' }}">
            {{ $supplierName ?: 'Direct Stock / Warehouse' }}
        </span>
    </div>
    
    @if($hasVariants)
        {{-- Variant Products: Added Date + Per-Color Inventory & Balance --}}
        <div style="font-size: 11px; color: #64748b; line-height: 1.2; display: flex; align-items: center; gap: 4px; margin-top: 1px;">
            <svg style="width: 12px; height: 12px; opacity: 0.7;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>Added: {{ $date }}</span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 5px; margin-top: 4px;">
            @foreach($variants as $v)
                @php
                    $vInv = (int) ($v->inventory_stock ?? 0);
                    $vShow = (int) ($v->show_quantity ?? $v->stock ?? 0);
                    $vBal = max(0, $vInv - $vShow);
                    $vBalColor = $vBal <= 5 ? '#ef4444' : '#eab308';
                    $dotColor = $getColorHex($v->color_name);
                @endphp
                <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; line-height: 1.35; white-space: nowrap;">
                    <span style="display: inline-block; width: 8px; height: 8px; min-width: 8px; border-radius: 50%; background-color: {{ $dotColor }}; box-shadow: 0 0 0 1px rgba(255,255,255,0.25); flex-shrink: 0;" title="{{ $v->color_name }}"></span>
                    <span style="font-weight: 600; color: #0284c7;">
                        Inventory Added: {{ $vInv }} units
                    </span>
                    <span style="opacity: 0.4;">•</span>
                    <span style="font-weight: 600; color: {{ $vBalColor }};">
                        Balance: {{ $vBal }} units
                    </span>
                </div>
            @endforeach
        </div>
    @else
        {{-- Standard Products: Single Aggregate without icon --}}
        <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: #64748b; line-height: 1.2;">
            <span style="display: inline-flex; align-items: center; gap: 3px;">
                <svg style="width: 12px; height: 12px; opacity: 0.7;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Added: {{ $date }}</span>
            </span>
            <span style="opacity: 0.5;">•</span>
            <span style="font-weight: 600; color: #0284c7;">
                Inventory Added: {{ $invStock }} units
            </span>
        </div>

        <div style="font-size: 11px; font-weight: 600; color: {{ $balanceColor }}; line-height: 1.2; margin-top: 2px;">
            Balance Inventory Stock: {{ $balanceStock }} units
        </div>
    @endif
</div>
