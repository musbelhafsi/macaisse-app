<?php

namespace App\Http\Controllers;

use App\Models\CashMovement;
use App\Models\CashRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CashMovementController extends Controller
{
    /**
     * Affiche la liste des mouvements de caisse avec filtres et totaux
     */
    public function index(Request $r)
    {
        $user = auth()->user();
        $cashId = $user?->current_cash_id;
        
        // Gestion cas où aucune caisse n'est sélectionnée
        if (!$cashId) {
            return view('movements.index', [
                'items' => collect(),
                'currentCash' => null,
                'totalEntrees' => 0,
                'totalSorties' => 0,
                'soldeFinal' => 0,
                'countOperations' => 0
            ])->with('error', 'Aucune caisse sélectionnée. Veuillez choisir une caisse.');
        }

        $currentCash = CashRegister::find($cashId);
        
        // Si la caisse n'existe pas
        if (!$currentCash) {
            return view('movements.index', [
                'items' => collect(),
                'currentCash' => null,
                'totalEntrees' => 0,
                'totalSorties' => 0,
                'soldeFinal' => 0,
                'countOperations' => 0
            ])->with('error', 'Caisse introuvable.');
        }

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
        
        return view('movements.index', compact(
            'items', 
            'currentCash', 
            'totalEntrees', 
            'totalSorties', 
            'soldeFinal',
            'countOperations'
        ));
    }

    /**
     * Affiche le formulaire d'édition d'un mouvement
     */
    public function edit(CashMovement $cashMovement)
    {
        $user = auth()->user();
        if ($cashMovement->cash_id !== $user->current_cash_id) {
            abort(403, 'Accès non autorisé.');
        }

        return view('movements.edit', compact('cashMovement'));
    }

    /**
     * Met à jour un mouvement et recalcule les balances
     */
    public function update(Request $request, CashMovement $cashMovement)
    {
        $user = auth()->user();
        if ($cashMovement->cash_id !== $user->current_cash_id) {
            abort(403, 'Accès non autorisé.');
        }

        $request->validate([
            'date_mvt' => 'required|date',
            'description' => 'required|string|max:255',
            'montant' => 'required|numeric|min:0.01',
        ]);

        return DB::transaction(function () use ($request, $cashMovement) {
            // Sauvegarder les anciennes valeurs pour comparer
            $ancienMontant = $cashMovement->montant;
            $ancienneDate = $cashMovement->date_mvt;
            
            // Mettre à jour le mouvement de caisse
            $cashMovement->update([
                'date_mvt' => $request->date_mvt,
                'description' => $request->description,
                'montant' => $request->montant,
            ]);

            // Mettre à jour l'entité source si elle existe
            if ($cashMovement->source) {
                $this->updateSourceEntity($cashMovement->source, $request);
            }

            // Recalculer les balances si le montant ou la date a changé
            if ($ancienMontant != $request->montant || $ancienneDate != $request->date_mvt) {
                $this->recalculerBalanceCaisse($cashMovement->cash_id, $cashMovement->id);
            }

            return redirect()->route('movements.index')
                ->with('success', 'Opération modifiée avec succès.');
        });
    }

    /**
     * Met à jour l'entité source selon son type
     */
    private function updateSourceEntity($source, $request)
    {
        $updateData = [
            'date' => $request->date_mvt,
            'montant' => $request->montant,
            'description' => $request->description,
        ];

        switch (get_class($source)) {
            case \App\Models\ContreBon::class:
                $source->update($updateData);
                break;
                
            case \App\Models\Expense::class:
                $source->update(array_merge($updateData, ['libelle' => $request->description]));
                break;
                
            case \App\Models\Transfer::class:
                // Pour les transferts, on met surtout à jour la description
                $source->update(['note' => $request->description]);
                break;
                
            default:
                // Pour les autres types, tentative de mise à jour standard
                if (isset($source->date)) $source->date = $request->date_mvt;
                if (isset($source->montant)) $source->montant = $request->montant;
                if (isset($source->description)) $source->description = $request->description;
                if (isset($source->libelle)) $source->libelle = $request->description;
                if (isset($source->note)) $source->note = $request->description;
                $source->save();
                break;
        }
    }

    /**
     * Recalcule les balances à partir d'un mouvement donné
     */
    private function recalculerBalanceCaisse($cashId, $apresMouvementId)
    {
        // Trouver le dernier mouvement avant celui modifié
        $mouvementPrecedent = CashMovement::where('cash_id', $cashId)
            ->where('id', '<', $apresMouvementId)
            ->orderBy('date_mvt', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        // Solde de départ
        $balanceCourante = $mouvementPrecedent ? $mouvementPrecedent->balance : 0;

        // Récupérer tous les mouvements à partir de celui modifié
        $mouvements = CashMovement::where('cash_id', $cashId)
            ->where('id', '>=', $apresMouvementId)
            ->orderBy('date_mvt')
            ->orderBy('id')
            ->get();

        // Recalculer chaque balance
        foreach ($mouvements as $mouvement) {
            $signe = in_array($mouvement->type, ['recette', 'transfert_credit']) ? 1 : -1;
            $balanceCourante += $signe * $mouvement->montant;
            
            // Mettre à jour seulement si la balance a changé (évite les updates inutiles)
            if (abs($mouvement->balance - $balanceCourante) > 0.001) {
                $mouvement->update(['balance' => round($balanceCourante, 2)]);
            }
        }

        // Mettre à jour le solde actuel de la caisse
        $dernierMouvement = CashMovement::where('cash_id', $cashId)
            ->orderBy('date_mvt', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($dernierMouvement) {
            CashRegister::where('id', $cashId)
                ->update(['balance' => $dernierMouvement->balance]);
        }
    }

    /**
     * Annule un mouvement (à implémenter si besoin)
     */
    public function annuler(Request $request, CashMovement $cashMovement)
    {
        $user = auth()->user();
        if ($cashMovement->cash_id !== $user->current_cash_id) {
            abort(403, 'Accès non autorisé.');
        }

        $request->validate([
            'raison_annulation' => 'required|string|max:255',
        ]);

    return DB::transaction(function () use ($request, $cashMovement, $user) {
            // Créer un mouvement inverse (contre-passation)
            $mouvementAnnulation = CashMovement::create([
                'cash_id' => $cashMovement->cash_id,
                'type' => 'annulation',
                'montant' => $cashMovement->montant,
                'source_type' => CashMovement::class,
                'source_id' => $cashMovement->id,
                'description' => 'ANNULATION: ' . $cashMovement->description . ' - Raison: ' . $request->raison_annulation,
                'date_mvt' => now(),
            ]);

            // Marquer le mouvement original comme annulé (champ optionnel)
            $cashMovement->update([
                'annule' => true,
                'raison_annulation' => $request->raison_annulation,
                'annule_le' => now(),
                'annule_par' => $user->id,
            ]);

            // Recalculer les balances à partir du mouvement d'annulation
            $this->recalculerBalanceCaisse($cashMovement->cash_id, $mouvementAnnulation->id);

            return redirect()->route('movements.index')
                ->with('success', 'Opération annulée avec succès.');
        });
    }

    /**
     * Affiche le formulaire de confirmation d'annulation
     */
    public function showAnnulationForm(CashMovement $cashMovement)
    {
        $user = auth()->user();
        if ($cashMovement->cash_id !== $user->current_cash_id) {
            abort(403, 'Accès non autorisé.');
        }

        // Vérifier si déjà annulé
        if ($cashMovement->annule) {
            return redirect()->route('movements.index')
                ->with('error', 'Cette opération est déjà annulée.');
        }

        return view('movements.annuler', compact('cashMovement'));
    }

    /**
     * Affiche les détails d'un mouvement
     */
    public function show(CashMovement $cashMovement)
    {
        $user = auth()->user();
        if ($cashMovement->cash_id !== $user->current_cash_id) {
            abort(403, 'Accès non autorisé.');
        }

        return view('movements.show', compact('cashMovement'));
    }
}
