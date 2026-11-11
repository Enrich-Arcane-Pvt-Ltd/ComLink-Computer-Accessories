<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Brand extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'slug',
        'url',
        'primary_hex',
        'logo',
        'description',
        'is_visible',
        'sort_order',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    protected $appends = [
        'logo_url',
    ];

    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getInitials(): string
    {
        $clean = preg_replace('/[^a-zA-Z0-9]/', '', $this->name ?? '');
        $initials = strtoupper(mb_substr($clean, 0, 2));

        return !empty($initials) ? $initials : 'BR';
    }

    public function getInitialsAvatarSvgUrl(): string
    {
        $initials = $this->getInitials();

        $palette = [
            ['bg' => '#1e293b', 'text' => '#38bdf8'], // Slate / Sky
            ['bg' => '#0f766e', 'text' => '#5eead4'], // Teal
            ['bg' => '#4338ca', 'text' => '#a5b4fc'], // Indigo
            ['bg' => '#6d28d9', 'text' => '#c4b5fd'], // Violet
            ['bg' => '#be185d', 'text' => '#f472b6'], // Pink
            ['bg' => '#b45309', 'text' => '#fde68a'], // Amber
            ['bg' => '#15803d', 'text' => '#86efac'], // Green
            ['bg' => '#0369a1', 'text' => '#7dd3fc'], // Blue
            ['bg' => '#334155', 'text' => '#f8fafc'], // Slate / Gray
        ];

        if (!empty($this->primary_hex) && preg_match('/^#[a-fA-F0-9]{6}$/', $this->primary_hex)) {
            $bg = $this->primary_hex;
            $text = '#ffffff';
        } else {
            $color = $palette[abs(crc32($this->name ?? '')) % count($palette)];
            $bg = $color['bg'];
            $text = $color['text'];
        }

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80">'
            . '<rect width="80" height="80" rx="40" fill="' . $bg . '"/>'
            . '<text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" fill="' . $text . '" font-family="ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif" font-size="30" font-weight="700" letter-spacing="1">'
            . htmlspecialchars($initials, ENT_XML1)
            . '</text>'
            . '</svg>';

        return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
    }

    public function getLogoUrlAttribute(): ?string
    {
        if ($this->logo) {
            if (str_starts_with($this->logo, 'http://') || str_starts_with($this->logo, 'https://')) {
                return $this->logo;
            }

            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($this->logo)) {
                return asset(\Illuminate\Support\Facades\Storage::url($this->logo));
            }

            if (file_exists(public_path($this->logo))) {
                return asset($this->logo);
            }
        }

        return $this->getInitialsAvatarSvgUrl();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'is_visible', 'url'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
