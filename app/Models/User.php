<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'customer_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'secondary_phone',
        'role',
        'status',
        'avatar_url',
        'is_guest',
        'email_verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_guest' => 'boolean',
        ];
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'name',
    ];

    protected static function booted()
    {
        static::created(function ($user) {
            $user->customer_id = 'VCUS' . str_pad($user->id, 5, '0', STR_PAD_LEFT);
            $user->saveQuietly();
        });
    }

    public function getNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function setNameAttribute($value)
    {
        $parts = explode(' ', trim((string) $value), 2);
        $this->attributes['first_name'] = $parts[0] ?? '';
        $this->attributes['last_name'] = $parts[1] ?? '';
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if ($this->avatar_url) {
            return Storage::url($this->avatar_url);
        }

        $name = trim($this->first_name . ' ' . $this->last_name);
        return "https://ui-avatars.com/api/?name={$name}&color=7F9CF5&background=EBF4FF";
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // active නැත්නම් කිසිම කෙනෙකුට බැහැ
        if ($this->status !== 'active') {
            return false;
        }

        // Admin panel එකට ඇතුල් විය හැක්කේ super-admin සහ admin ට පමණි
        if ($panel->getId() === 'admin') {
            return in_array($this->role, ['super-admin', 'admin']);
        }

        return false;
    }

    public function getFilamentName(): string
    {
        return trim("{$this->first_name} {$this->last_name}") ?: $this->username ?: 'User';
    }


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'role', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
