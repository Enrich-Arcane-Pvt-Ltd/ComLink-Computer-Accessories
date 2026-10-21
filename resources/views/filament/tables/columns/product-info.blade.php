<div style="display: flex; align-items: center; gap: 10px; padding: 2px 0;">
    @php
        $record = $getRecord();
        $images = $record->images;
        $img = is_array($images) && count($images) > 0 ? $images[0] : null;
        $imgUrl = $img ? (str_starts_with($img, 'http') ? $img : asset('storage/' . $img)) : asset('images/placeholder.png');
        $cat = $record->categories->firstWhere('parent_id', null);
        $subCat = $record->categories->firstWhere('parent_id', '!=', null);
        $condition = $record->condition ?? 'Brand New';
        $variants = $record->variants ?? collect();
        $hasVariants = $record->hasColorVariants() && $variants->isNotEmpty();
    @endphp
    <img 
        src="{{ $imgUrl }}" 
        alt="{{ $record->name }}" 
        style="width: 38px; height: 38px; min-width: 38px; min-height: 38px; max-width: 38px; max-height: 38px; object-fit: cover; border-radius: 6px; border: 1px solid rgba(156, 163, 175, 0.3); background-color: white;" 
        onerror="this.src='/images/placeholder.png'" 
    />
    <div style="display: flex; flex-direction: column; min-width: 0;">
        <div style="display: flex; align-items: center; gap: 6px;">
            <span style="font-weight: 600; font-size: 13px; line-height: 1.25; color: inherit; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 320px;" title="{{ $record->name }}">
                {{ $record->name }}
            </span>
        </div>

        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 4px; margin-top: 3px;">
            @if($cat)
                <span style="display: inline-flex; align-items: center; padding: 1px 6px; border-radius: 4px; font-size: 10px; font-weight: 600; background-color: rgba(6, 182, 212, 0.15); color: #0891b2; border: 1px solid rgba(6, 182, 212, 0.3);">
                    {{ $cat->name }}
                </span>
            @endif
            @if($subCat)
                <span style="display: inline-flex; align-items: center; padding: 1px 6px; border-radius: 4px; font-size: 10px; font-weight: 600; background-color: rgba(156, 163, 175, 0.15); color: #4b5563; border: 1px solid rgba(156, 163, 175, 0.3);">
                    {{ $subCat->name }}
                </span>
            @endif
            @if(! $hasVariants)
                <span style="display: inline-flex; align-items: center; padding: 1px 6px; border-radius: 4px; font-size: 10px; font-weight: 600; {{ $condition === 'Used' ? 'background-color: rgba(245, 158, 11, 0.15); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.3);' : 'background-color: rgba(16, 185, 129, 0.15); color: #059669; border: 1px solid rgba(16, 185, 129, 0.3);' }}">
                    {{ $condition }}
                </span>
            @endif
        </div>
    </div>
</div>
