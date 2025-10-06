<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContreBon extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero','company_id','livreur_id','date','montant','nombre_bons','ecart','note',
        'validated_at','validated_by','validated_cash_id','closed'
    ];

    public function company() { return $this->belongsTo(Company::class); }
    public function livreur() { return $this->belongsTo(User::class, 'livreur_id'); }
    public function bons() { return $this->hasMany(RecouvrementBon::class); }
}