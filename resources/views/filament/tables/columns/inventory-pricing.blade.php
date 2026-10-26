@php
    $record = $getRecord();
    $latestSelling = $record?->latest_price;
    $latestGrn = $record?->latest_grn_price;
    
    $margin = ($latestSelling && $latestGrn) ? ($latestSelling - $latestGrn) : null;
@endphp

<div style="display: flex; flex-direction: column; min-width: 0; padding: 2px 0; gap: 2px;">
    @if($latestSelling !== null)
        <div style="display: flex; align-items: baseline; gap: 4px;">
            <span style="font-size: 10px; font-weight: 600; text-transform: uppercase; color: #64748b;">Selling:</span>
            <span style="font-size: 12px; font-weight: 800; font-family: monospace; color: #06b6d4;">
                Rs. {{ number_format((float) $latestSelling, 2) }}
            </span>
        </div>
    @else
        <span style="font-size: 11px; color: #94a3b8; font-style: italic;">Not on storefront</span>
    @endif

    @if($latestGrn !== null)
        <div style="display: flex; align-items: baseline; gap: 4px; font-size: 11px;">
            <span style="font-size: 10px; font-weight: 600; text-transform: uppercase; color: #64748b;">Cost:</span>
            <span style="font-family: monospace; color: #94a3b8; font-weight: 600;">
                Rs. {{ number_format((float) $latestGrn, 2) }}
            </span>
            @if($margin !== null && $margin > 0)
                <span style="font-size: 10px; font-weight: 700; color: #10b981;">
                    (+Rs. {{ number_format((float) $margin, 0) }})
                </span>
            @endif
        </div>
    @endif
</div>
