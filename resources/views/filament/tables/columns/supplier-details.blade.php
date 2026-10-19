@php
    $record = $getRecord();
    $supplierName = $record->supplier?->name;
    $date = $record->stock_added_date 
        ? \Carbon\Carbon::parse($record->stock_added_date)->format('M d, Y') 
        : ($record->created_at ? $record->created_at->format('M d, Y') : 'N/A');
    $addedCount = $record->stock_added_count ?? $record->quantity ?? 0;
@endphp

<div style="display: flex; flex-direction: column; min-width: 0; padding: 2px 0;">
    <div style="display: flex; align-items: center; gap: 6px;">
        <svg style="width: 14px; height: 14px; color: #06b6d4; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
        <span style="font-weight: 700; font-size: 13px; color: inherit; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 260px;" title="{{ $supplierName ?? 'Direct Stock' }}">
            {{ $supplierName ?: 'Direct Stock / Warehouse' }}
        </span>
    </div>
    
    <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px; font-size: 11px; color: #64748b; line-height: 1.2;">
        <span style="display: inline-flex; align-items: center; gap: 3px;">
            <svg style="width: 12px; height: 12px; opacity: 0.7;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>Added: {{ $date }}</span>
        </span>
        <span style="opacity: 0.5;">•</span>
        <span style="display: inline-flex; align-items: center; gap: 3px; font-weight: 600; color: #0284c7;">
            <svg style="width: 12px; height: 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <span>Added: {{ $addedCount }} units</span>
        </span>
    </div>
</div>
