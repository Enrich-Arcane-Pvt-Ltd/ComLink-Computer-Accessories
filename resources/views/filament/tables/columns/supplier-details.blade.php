@php
    $record = $getRecord();
    $supplier = $record->supplier;
    $supplierName = $supplier?->name;
    
    // Product or GRN added date
    if ($record->product_code) {
        $latestGrn = \App\Models\GrnItem::where('product_code', $record->product_code)->latest('id')->first();
        $rawDate = $latestGrn?->created_at ?? $record->stock_added_date ?? $record->created_at;
    } else {
        $rawDate = $record->stock_added_date ?? $record->created_at;
    }
    $date = $rawDate ? \Carbon\Carbon::parse($rawDate)->format('M d, Y') : 'N/A';
@endphp

<div style="display: flex; flex-direction: column; min-width: 0; padding: 2px 0; gap: 4px;">
    {{-- Supplier Name --}}
    <div style="display: flex; align-items: center; gap: 6px;">
        <svg style="width: 14px; height: 14px; color: #06b6d4; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
        <span style="font-weight: 700; font-size: 13px; color: inherit; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 260px;" title="{{ $supplierName ?? 'Direct Stock' }}">
            {{ $supplierName ?: 'Direct Stock / Warehouse' }}
        </span>
    </div>

    {{-- Contact info if available --}}
    @if($supplier && ($supplier->contact_no || $supplier->email))
        <div style="font-size: 11px; color: #64748b; line-height: 1.2; display: flex; align-items: center; gap: 6px;">
            @if($supplier->contact_no)
                <span>{{ $supplier->contact_no }}</span>
            @endif
            @if($supplier->contact_no && $supplier->email)
                <span>•</span>
            @endif
            @if($supplier->email)
                <span class="truncate max-w-[160px]" title="{{ $supplier->email }}">{{ $supplier->email }}</span>
            @endif
        </div>
    @endif

    {{-- Added Date --}}
    <div style="display: flex; align-items: center; gap: 4px; font-size: 11px; color: #64748b; line-height: 1.2;">
        <svg style="width: 12px; height: 12px; opacity: 0.7;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <span>Added: {{ $date }}</span>
    </div>
</div>
