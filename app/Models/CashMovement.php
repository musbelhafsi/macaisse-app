<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CashMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_id','type','montant','balance','source_type','source_id','description','date_mvt'
    ];

    // Polymorphic relation to the source document (Expense, Transfer, ContreBon, ...)
    public function source()
    {
        return $this->morphTo(null, 'source_type', 'source_id');
    }

    protected static function booted(): void
    {
        static::creating(function (CashMovement $m) {
            // Compute new balance per cash register at the movement's date
            // We consider balance as previous latest balance +/- montant depending on type semantics.
            // Convention: montant is signed by type: recette/transfert_credit increases, depense/transfert_debit/ajustement decreases.
            $sign = in_array($m->type, ['recette','transfert_credit']) ? 1 : -1;

            // Fetch last balance for this cash_id, before or at date_mvt.
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