<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero','from_cash_id','to_cash_id','montant','montant_recu','ecart','statut','emitted_at','validated_at','note'
    ];
}