<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecouvrementBon extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero','company_id','livreur_id','date_recouvrement','client_id','montant','type','note','contre_bon_id'
    ];

    // Relations
    public function client() { return $this->belongsTo(Client::class); }
    public function company() { return $this->belongsTo(Company::class); }
    public function livreur() { return $this->belongsTo(User::class, 'livreur_id'); }
    public function contreBon() { return $this->belongsTo(ContreBon::class, 'contre_bon_id'); }
}