<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashRegister extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'is_main', 'is_bank', 'currency', 'balance'];

    // Users having access to this cash register
    public function users()
    {
        return $this->belongsToMany(User::class, 'cash_register_user');
    }
}