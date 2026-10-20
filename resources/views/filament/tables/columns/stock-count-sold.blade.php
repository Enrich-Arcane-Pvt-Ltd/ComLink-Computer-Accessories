@php
    $product = $getRecord();
    $sold = (int) $product->sold_count;
@endphp

<div style="display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 2px 0;">
    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 9999px; font-size: 11px; font-weight: 700; {{ $sold > 0 ? 'background-color: rgba(99, 102, 241, 0.15); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.3);' : 'background-color: rgba(113, 113, 122, 0.15); color: #a1a1aa; border: 1px solid rgba(113, 113, 122, 0.25);' }}">
        {{ number_format($sold) }} {{ $sold === 1 ? 'unit' : 'units' }}
    </span>
    <span style="font-size: 10px; color: #71717a; white-space: nowrap;">
        {{ $sold > 0 ? 'Paid Orders' : 'No sales yet' }}
    </span>
</div>
