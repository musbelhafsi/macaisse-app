<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CashMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_id','type','montant','balance','source_type','source_id','description','date_mvt',
        'annule','raison_annulation','annule_le','annule_par' // Nouveaux champs
    ];

    protected $casts = [
        'date_mvt' => 'datetime',
        'annule_le' => 'datetime',
        'annule' => 'boolean',
    ];

    // Relation polymorphique
    public function source()
    {
        return $this->morphTo(null, 'source_type', 'source_id');
    }

    // Relation avec l'utilisateur qui a annulé
    public function annulePar()
    {
        return $this->belongsTo(User::class, 'annule_par');
    }

    // Scope pour exclure les mouvements annulés
    public function scopeNonAnnule($query)
    {
        return $query->where('annule', false)->orWhereNull('annule');
    }

    protected static function booted(): void
    {
        static::creating(function (CashMovement $m) {
            // Pour les annulations, le signe est inversé
            if ($m->type === 'annulation') {
                $sign = -1; // L'annulation compense le mouvement original
            } else {
                $sign = in_array($m->type, ['recette','transfert_credit']) ? 1 : -1;
            }

            $prev = DB::table('cash_movements')
                ->where('cash_id', $m->cash_id)
                ->where('date_mvt', '<=', $m->date_mvt)
                ->orderByDesc('date_mvt')
                ->orderByDesc('id')
                ->value('balance');

            $prev = $prev !== null ? (float)$prev : 0.0;
            $m->balance = round($prev + ($sign * (float)$m->montant), 2);
        });
    }
}
