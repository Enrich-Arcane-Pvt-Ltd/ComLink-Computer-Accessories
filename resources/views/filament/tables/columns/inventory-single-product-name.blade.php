@php
    $record = $getRecord();
    $code = $record?->product_code;
    $name = $record?->product_name ?? 'Unknown Product';
    
    // Check if storefront product exists for condition & categories
    $storeProduct = $record?->storefrontProduct;
    $condition = $storeProduct?->condition ?? $record?->grnItems()->latest('id')->value('product_condition') ?? 'Brandnew';
    $isBrandNew = in_array(strtolower($condition), ['brandnew', 'brand new']);
    $condBg = $isBrandNew ? 'rgba(16, 185, 129, 0.15)' : 'rgba(245, 158, 11, 0.15)';
    $condColor = $isBrandNew ? '#10b981' : '#f59e0b';
    $condBorder = $isBrandNew ? 'rgba(16, 185, 129, 0.3)' : 'rgba(245, 158, 11, 0.3)';
    
    $batchesCount = $record?->grnItems()->count() ?? 0;
@endphp

<div style="display: flex; flex-direction: column; gap: 4px; padding: 2px 0; min-width: 240px;">
    @if($record)
        {{-- Product Name --}}
        <span style="font-weight: 700; font-size: 13px; color: inherit; line-height: 1.3;" title="{{ $name }}">
            {{ $name }}
        </span>

        {{-- Badges row: Code, Condition, Batches --}}
        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-top: 2px;">
            @if(!empty($code))
                <span style="display: inline-flex; align-items: center; padding: 1px 7px; border-radius: 4px; font-size: 10px; font-weight: 700; font-family: monospace; background-color: rgba(6, 182, 212, 0.12); color: #06b6d4; border: 1px solid rgba(6, 182, 212, 0.28);">
                    Code: {{ $code }}
                </span>
            @endif

            <span style="display: inline-flex; align-items: center; padding: 1px 7px; border-radius: 4px; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; background-color: {{ $condBg }}; color: {{ $condColor }}; border: 1px solid {{ $condBorder }};">
                {{ $condition }}
            </span>

            <span style="display: inline-flex; align-items: center; padding: 1px 6px; border-radius: 4px; font-size: 10px; font-weight: 600; background-color: rgba(99, 102, 241, 0.12); color: #6366f1; border: 1px solid rgba(99, 102, 241, 0.28);">
                {{ $batchesCount }} {{ $batchesCount === 1 ? 'Stock Batch' : 'Stock Batches' }}
            </span>
        </div>
    @endif
</div>
