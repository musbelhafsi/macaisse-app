<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cheque extends Model
{
    use HasFactory;

    protected $fillable = [
        'code_banque','numero','client_id','company_id','livreur_id','montant','echeance','date_recouvrement','statut','contre_bon_id'
    ];

    public function client() { return $this->belongsTo(Client::class); }
    public function company() { return $this->belongsTo(Company::class); }
    public function livreur() { return $this->belongsTo(User::class, 'livreur_id'); }
    public function contreBon() { return $this->belongsTo(ContreBon::class, 'contre_bon_id'); }
}