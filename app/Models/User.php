<?php

namespace App\Models;

use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Panel;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable //implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
        ];
    }

    // public function canAccessPanel(Panel $panel): bool
    // {
    //     // Assuming 'super_admin' can access any panel
    //     if ($this->hasRole('super_admin')) {
    //         return true;
    //     }

    //     // Define other role-based access logic
    //     if ($panel->getId() === 'admin') {
    //         return $this->hasRole('admin');
    //     }

    //     if ($panel->getId() === 'moderator') {
    //         return $this->hasRole('moderator');
    //     }

    //     // Default deny access
    //     return true;
    // }
}
