<?php

namespace App\Http\Controllers;

use App\Models\CashMovement;
use App\Models\CashRegister;
use Illuminate\Http\Request;

class CashMovementController extends Controller
{
    public function index(Request $r)
    {
        $user = auth()->user();
        $cashId = $user?->current_cash_id;
        abort_unless($cashId, 403, 'Aucune caisse courante sélectionnée.');

        // Query pour les données paginées
        $query = CashMovement::with('source')
            ->where('cash_id', $cashId)
            ->orderByDesc('id')
            ->orderBy('date_mvt');

        // Query pour les totaux (sans pagination)
        $totalsQuery = CashMovement::where('cash_id', $cashId);

        // Appliquer les mêmes filtres aux deux queries
        if ($r->filled('type')) {
            $query->where('type', $r->type);
            $totalsQuery->where('type', $r->type);
        }
        
        if ($r->filled('from') && $r->filled('to')) {
            $query->whereBetween('date_mvt', [$r->from, $r->to]);
            $totalsQuery->whereBetween('date_mvt', [$r->from, $r->to]);
        }
        
        if ($r->filled('search')) {
            $s = strtoupper($r->search);
            $query->where(function($q) use ($s) {
                $q->where('description', 'like', "%$s%")
                  ->orWhere('montant', 'like', "%$s%")
                  ->orWhere('balance', 'like', "%$s%");
            });
            $totalsQuery->where(function($q) use ($s) {
                $q->where('description', 'like', "%$s%")
                  ->orWhere('montant', 'like', "%$s%")
                  ->orWhere('balance', 'like', "%$s%");
            });
        }

        // Calculer les totaux de manière optimisée
        $totalEntrees = (clone $totalsQuery)
            ->where(function($q) {
                $q->whereIn('type', ['recette', 'transfert_credit'])
                  ->orWhere(function($q2) {
                      $q2->where('type', 'ajustement')->where('montant', '>=', 0);
                  });
            })
            ->sum('montant');

        $totalSorties = (clone $totalsQuery)
            ->where(function($q) {
                $q->whereIn('type', ['depense', 'transfert_debit'])
                  ->orWhere(function($q2) {
                      $q2->where('type', 'ajustement')->where('montant', '<', 0);
                  });
            })
            ->sum('montant');

        $soldeFinal = $totalEntrees - $totalSorties;
        $countOperations = $totalsQuery->count();

        $items = $query->paginate(50)->appends($r->query());
        $currentCash = CashRegister::find($cashId);
        
        return view('movements.index', compact(
            'items', 
            'currentCash', 
            'totalEntrees', 
            'totalSorties', 
            'soldeFinal',
            'countOperations'
        ));
    }
}
