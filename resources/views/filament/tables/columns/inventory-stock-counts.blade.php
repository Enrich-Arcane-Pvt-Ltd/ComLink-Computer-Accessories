@php
    $record = $getRecord();
    $totalStock = $record?->total_stock ?? 0;
    $sold = $record?->sold_count ?? 0;
    $available = $record?->available_stock ?? max(0, $totalStock - $sold);

    $isOutOfStock = $available <= 0;
    $isLowStock = $available > 0 && $available < 5;
    $availColor = $isOutOfStock ? '#ef4444' : ($isLowStock ? '#f59e0b' : '#10b981');
    $availBg = $isOutOfStock ? 'rgba(239, 68, 68, 0.12)' : ($isLowStock ? 'rgba(245, 158, 11, 0.12)' : 'rgba(16, 185, 129, 0.12)');
    $availBorder = $isOutOfStock ? 'rgba(239, 68, 68, 0.25)' : ($isLowStock ? 'rgba(245, 158, 11, 0.25)' : 'rgba(16, 185, 129, 0.25)');
@endphp

<div style="display: flex; flex-direction: column; min-width: 0; padding: 2px 0; gap: 4px;">
    {{-- Stock & Sold row --}}
    <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; line-height: 1.3;">
        <span style="display: inline-flex; align-items: center; gap: 3px; font-weight: 600; color: #0284c7;">
            <svg style="width: 12px; height: 12px; opacity: 0.8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            Received: {{ $totalStock }}
        </span>
        <span style="opacity: 0.4;">•</span>
        <span style="display: inline-flex; align-items: center; gap: 3px; font-weight: 600; color: #a855f7;">
            <svg style="width: 12px; height: 12px; opacity: 0.8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            Sold: {{ $sold }}
        </span>
    </div>

    {{-- Available Status Badge --}}
    <div style="display: inline-flex; align-items: center; gap: 4px;">
        <span style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; background-color: {{ $availBg }}; color: {{ $availColor }}; border: 1px solid {{ $availBorder }};">
            <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background-color: {{ $availColor }};"></span>
            @if($isOutOfStock)
                Available: 0 (Sold Out)
            @elseif($isLowStock)
                Available: {{ $available }} left (Low)
            @else
                Available: {{ $available }} units
            @endif
        </span>
    </div>
</div>
