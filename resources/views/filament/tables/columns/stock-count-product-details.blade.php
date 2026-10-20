@php
    $product = $getRecord();
    $images = is_array($product->images) ? $product->images : [];
    $img = count($images) > 0 ? $images[0] : null;
    $imgUrl = $img 
        ? ((str_starts_with($img, 'http://') || str_starts_with($img, 'https://') || str_starts_with($img, '/')) ? $img : asset('storage/' . $img))
        : '/images/placeholder.png';

    $condition = $product->condition ?? 'Brand New';
    $isUsed = in_array(strtolower($condition), ['used', 'second hand', 'secondhand']);
    $conditionStyle = $isUsed 
        ? 'background-color: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3);'
        : 'background-color: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3);';
@endphp

<div style="display: flex; align-items: center; gap: 10px; padding: 3px 0;">
    {{-- Thumbnail --}}
    <img 
        src="{{ $imgUrl }}" 
        alt="{{ $product->name }}" 
        style="width: 38px; height: 38px; min-width: 38px; min-height: 38px; max-width: 38px; max-height: 38px; object-fit: cover; border-radius: 6px; border: 1px solid rgba(156, 163, 175, 0.25); background-color: #27272a;" 
        onerror="this.src='/images/placeholder.png'" 
    />

    {{-- Details --}}
    <div style="display: flex; flex-direction: column; min-width: 0;">
        <span style="font-weight: 600; font-size: 13px; line-height: 1.25; color: inherit; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 340px;" title="{{ $product->name }}">
            {{ $product->name }}
        </span>
        <div style="display: flex; align-items: center; gap: 6px; margin-top: 3px;">
            <span style="display: inline-flex; align-items: center; padding: 1px 6px; border-radius: 4px; font-size: 10px; font-weight: 600; {{ $conditionStyle }}">
                {{ $condition }}
            </span>
            @if($product->product_code)
                <span style="display: inline-flex; align-items: center; padding: 1px 6px; border-radius: 4px; font-size: 10px; font-weight: 600; font-family: monospace; background-color: rgba(6, 182, 212, 0.15); color: #06b6d4; border: 1px solid rgba(6, 182, 212, 0.3);" title="Product Code">
                    {{ $product->product_code }}
                </span>
            @endif
            @if($product->supplier)
                <span style="font-size: 11px; color: #71717a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 160px;" title="Supplier: {{ $product->supplier->name }}">
                    &bull; {{ $product->supplier->name }}
                </span>
            @endif
        </div>
    </div>
</div>
