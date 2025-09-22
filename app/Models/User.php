<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'current_cash_id'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relations
    public function cashes()
    {
        return $this->belongsToMany(CashRegister::class, 'cash_register_user');
    }

    public function currentCash()
    {
        return $this->belongsTo(CashRegister::class, 'current_cash_id');
    }

    // Helpers roles
    public function hasRole(string $role): bool { return $this->role === $role; }
    public function canSwitchToCash(int $cashId): bool { return $this->cashes()->where('cash_register_id', $cashId)->exists(); }
}
