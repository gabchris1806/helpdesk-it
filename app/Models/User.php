<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'master_lapors_id',
        'permissions',
        'password',
        'theme_mode',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
        ];
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'admin') {
            return true;
        }

        if (in_array('*', $this->permissions ?? [], true)) {
            return true;
        }

        return in_array($permission, $this->permissions ?? [], true);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || in_array('*', $this->permissions ?? [], true);
    }

    public function masterLapor()
    {
        return $this->belongsTo(MasterLapor::class, 'master_lapors_id');
    }

    /**
     * Hanya user dengan minimal satu permission admin yang dapat mengakses panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }
}
