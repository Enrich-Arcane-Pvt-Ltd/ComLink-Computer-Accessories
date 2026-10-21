@php
    $record = $getRecord();
    $items = $record?->items ?? collect();

    $getColorHex = function (?string $name): string {
        if (! $name) return '#94a3b8';
        $n = strtolower(trim($name));
        if (str_contains($n, 'orange')) return '#f97316';
        if (str_contains($n, 'yellow')) return '#eab308';
        if (str_contains($n, 'blue') || str_contains($n, 'cyan') || str_contains($n, 'navy')) return '#3b82f6';
        if (str_contains($n, 'red') || str_contains($n, 'crimson') || str_contains($n, 'ruby')) return '#ef4444';
        if (str_contains($n, 'green') || str_contains($n, 'emerald') || str_contains($n, 'lime')) return '#22c55e';
        if (str_contains($n, 'purple') || str_contains($n, 'violet')) return '#a855f7';
        if (str_contains($n, 'pink') || str_contains($n, 'rose')) return '#ec4899';
        if (str_contains($n, 'black') || str_contains($n, 'dark')) return '#27272a';
        if (str_contains($n, 'white')) return '#f4f4f5';
        if (str_contains($n, 'gray') || str_contains($n, 'grey')) return '#71717a';
        if (str_contains($n, 'silver')) return '#94a3b8';
        if (str_contains($n, 'gold')) return '#eab308';
        if (str_contains($n, 'brown')) return '#854d0e';
        
        $hash = abs(crc32($n));
        $hue = $hash % 360;
        return "hsl({$hue}, 75%, 50%)";
    };

    $hexToRgba = function (string $hex, float $alpha = 0.15): string {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 6) {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            return "rgba({$r}, {$g}, {$b}, {$alpha})";
        }
        return "rgba(148, 163, 184, {$alpha})";
    };
@endphp

<div style="display: flex; flex-direction: column; gap: 7px; padding: 3px 0; min-width: 280px;">
    @foreach($items as $item)
        @php
            $colorHex = !empty($item->color_name) ? $getColorHex($item->color_name) : null;
            $isBrandNew = in_array(strtolower($item->product_condition ?? ''), ['brandnew', 'brand new']);
            $condBg = $isBrandNew ? 'rgba(16, 185, 129, 0.15)' : 'rgba(245, 158, 11, 0.15)';
            $condColor = $isBrandNew ? '#10b981' : '#f59e0b';
            $condBorder = $isBrandNew ? 'rgba(16, 185, 129, 0.3)' : 'rgba(245, 158, 11, 0.3)';
        @endphp
        <div style="display: flex; flex-direction: column; gap: 4px; padding-bottom: 6px; border-bottom: 1px solid rgba(255, 255, 255, 0.08);" class="last:border-b-0 last:pb-0">
            {{-- Top line: Product Name, Color Badge, and Quantity --}}
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                <div style="display: flex; align-items: center; gap: 6px; min-width: 0; flex-wrap: wrap;">
                    <span style="font-weight: 600; font-size: 13px; color: inherit; line-height: 1.3;" title="{{ $item->product_name }}">
                        {{ $item->product_name }}
                    </span>
                    @if(!empty($item->color_name))
                        <span style="display: inline-flex; align-items: center; gap: 4.5px; padding: 1px 7px; border-radius: 4px; font-size: 10px; font-weight: 600; background-color: {{ $hexToRgba($colorHex, 0.15) }}; color: {{ $colorHex }}; border: 1px solid {{ $hexToRgba($colorHex, 0.3) }};">
                            <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background-color: {{ $colorHex }}; box-shadow: 0 0 0 1px rgba(255,255,255,0.25);"></span>
                            {{ $item->color_name }}
                        </span>
                    @endif
                </div>
                <span style="display: inline-flex; align-items: center; padding: 1.5px 7px; border-radius: 4px; font-size: 11px; font-weight: 700; font-family: monospace; background-color: rgba(6, 182, 212, 0.15); color: #06b6d4; border: 1px solid rgba(6, 182, 212, 0.3); white-space: nowrap; flex-shrink: 0;">
                    &times; {{ $item->product_quantity }}
                </span>
            </div>

            {{-- Bottom line: Condition and Product Code --}}
            <div style="display: flex; align-items: center; gap: 6px; margin-top: 1px;">
                @if(!empty($item->product_condition))
                    <span style="display: inline-flex; align-items: center; padding: 1px 7px; border-radius: 4px; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; background-color: {{ $condBg }}; color: {{ $condColor }}; border: 1px solid {{ $condBorder }};">
                        {{ $item->product_condition }}
                    </span>
                @endif
                @if(!empty($item->product_code))
                    <span style="display: inline-flex; align-items: center; padding: 1px 7px; border-radius: 4px; font-size: 10px; font-weight: 600; font-family: monospace; background-color: rgba(148, 163, 184, 0.12); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.25);">
                        {{ $item->product_code }}
                    </span>
                @endif
            </div>
        </div>
    @endforeach
</div>
