@php
    $product = $getRecord();
    $current = (int) $product->current_inventory_stock;
    $warehouseBalance = (int) $product->balance_inventory_stock;
    $webStock = (int) ($product->quantity ?? 0);

    $badgeStyle = match(true) {
        $current === 0 => 'background-color: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3);',
        $current <= 5 => 'background-color: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3);',
        default => 'background-color: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3);',
    };

    $dotColor = match(true) {
        $current === 0 => '#ef4444',
        $current <= 5 => '#f59e0b',
        default => '#10b981',
    };
@endphp

<div style="display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 2px 0;">
    <span style="display: inline-flex; align-items: center; gap: 5px; padding: 2px 8px; border-radius: 9999px; font-size: 11px; font-weight: 700; {{ $badgeStyle }}">
        <span style="width: 6px; height: 6px; border-radius: 9999px; background-color: {{ $dotColor }}; flex-shrink: 0;"></span>
        {{ number_format($current) }} {{ $current === 1 ? 'unit' : 'units' }}
    </span>
    <span style="font-size: 10px; color: #71717a; white-space: nowrap;">
        Wh: <strong style="color: inherit; font-weight: 600;">{{ $warehouseBalance }}</strong> &bull; Web: <strong style="color: inherit; font-weight: 600;">{{ $webStock }}</strong>
    </span>
</div>
