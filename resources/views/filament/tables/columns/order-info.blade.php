<div style="display: flex; align-items: center; gap: 12px; padding: 4px 0;">
    @php
        $record = $getRecord();
        $firstItem = $record->items->first();
        $img = $firstItem?->product_image;
        $imgUrl = $img ? ((str_starts_with($img, 'http') || str_starts_with($img, '/')) ? $img : asset('storage/' . $img)) : asset('images/placeholder.png');
        $totalUnits = (int) $record->items->sum('quantity');
        $totalProducts = (int) $record->items->count();
    @endphp
    <img 
        src="{{ $imgUrl }}" 
        alt="{{ $record->order_number }}" 
        style="width: 44px; height: 44px; min-width: 44px; min-height: 44px; max-width: 44px; max-height: 44px; object-fit: cover; border-radius: 8px; border: 1px solid rgba(156, 163, 175, 0.3); background-color: #1e293b;" 
        onerror="this.src='/images/placeholder.png'" 
    />
    <div style="display: flex; flex-direction: column; min-width: 0;">
        <span style="font-weight: 700; font-size: 13px; font-family: monospace; letter-spacing: 0.05em; color: #06b6d4;">
            {{ $record->order_number }}
        </span>
        <span style="font-size: 11px; color: #94a3b8; margin-top: 2px;">
            {{ $totalUnits }} {{ $totalUnits === 1 ? 'unit' : 'units' }} ({{ $totalProducts }} {{ $totalProducts === 1 ? 'product' : 'products' }})
        </span>
    </div>
</div>
