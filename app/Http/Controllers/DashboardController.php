<?php

namespace App\Http\Controllers;

use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\Cheque;
use App\Models\ContreBon;
use App\Models\Expense;
use App\Models\Transfer;
use App\Models\RecouvrementBon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $cashId = $user?->current_cash_id;
        $currentCash = $cashId ? CashRegister::find($cashId) : null;

        // Période (par défaut: mois en cours)
        $from = $request->filled('from') ? Carbon::parse($request->get('from'))->startOfDay() : Carbon::now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->get('to'))->endOfDay() : Carbon::now()->endOfDay();

        $stats = $this->getDashboardStats($cashId, $from, $to);
        $charts = $this->getChartsData($cashId, $from, $to);
        $recentActivities = $this->getRecentActivities($cashId);

        return view('dashboard', array_merge([
            'currentCash' => $currentCash,
            'periodFrom' => $from->format('Y-m-d'),
            'periodTo' => $to->format('Y-m-d'),
        ], $stats, $charts, $recentActivities));
    }

    private function getDashboardStats($cashId, $from, $to)
    {
        if (!$cashId) {
            return $this->getEmptyStats();
        }

        return DB::transaction(function () use ($cashId, $from, $to) {
            // SOLDE ET MOUVEMENTS
            $currentBalance = CashMovement::where('cash_id', $cashId)
                ->orderByDesc('date_mvt')
                ->orderByDesc('id')
                ->value('balance') ?? 0;

            $movementsToday = CashMovement::where('cash_id', $cashId)
                ->whereDate('date_mvt', Carbon::today())
                ->count();

            $movementsPeriod = CashMovement::where('cash_id', $cashId)
                ->whereBetween('date_mvt', [$from, $to])
                ->count();

            // FLUX FINANCIERS
            $flux = CashMovement::where('cash_id', $cashId)
                ->whereBetween('date_mvt', [$from, $to])
                ->selectRaw("
                    SUM(CASE WHEN type IN ('recette','transfert_credit') THEN montant ELSE 0 END) as entrées,
                    SUM(CASE WHEN type IN ('depense','transfert_debit') THEN montant ELSE 0 END) as sorties,
                    SUM(CASE WHEN type = 'ajustement' AND montant >= 0 THEN montant ELSE 0 END) as ajustements_positifs,
                    SUM(CASE WHEN type = 'ajustement' AND montant < 0 THEN ABS(montant) ELSE 0 END) as ajustements_negatifs
                ")->first();

            $entrees = ($flux->entrées ?? 0) + ($flux->ajustements_positifs ?? 0);
            $sorties = ($flux->sorties ?? 0) + ($flux->ajustements_negatifs ?? 0);
            $fluxNet = $entrees - $sorties;

            // STATISTIQUES PAR TYPE
            $byType = CashMovement::where('cash_id', $cashId)
                ->whereBetween('date_mvt', [$from, $to])
                ->select('type', DB::raw('COUNT(*) as count'), DB::raw('SUM(montant) as total'))
                ->groupBy('type')
                ->orderByDesc('total')
                ->get();

            // CONTRE-BONS ET RECOUVREMENTS
            $contreBonsStats = ContreBon::whereBetween('date', [$from, $to])
                ->selectRaw("
                    COUNT(*) as total,
                    SUM(montant) as montant_total,
                    AVG(montant) as montant_moyen,
                    SUM(ecart) as ecart_total
                ")->first();

            $bonsRecouvrement = RecouvrementBon::whereBetween('date_recouvrement', [$from, $to])
                ->selectRaw("
                    COUNT(*) as total,
                    SUM(montant) as montant_total,
                    COUNT(DISTINCT client_id) as clients_uniques
                ")->first();

            // DÉPENSES
            $depensesStats = Expense::where('cash_id', $cashId)
                ->whereBetween('date', [$from, $to])
                ->selectRaw("
                    COUNT(*) as total,
                    SUM(montant) as montant_total,
                    AVG(montant) as montant_moyen
                ")->first();

            // TRANSFERTS
            $transfertsStats = Transfer::where(function($q) use ($cashId) {
                    $q->where('from_cash_id', $cashId)->orWhere('to_cash_id', $cashId);
                })
                ->whereBetween('emitted_at', [$from, $to])
                ->selectRaw("
                    COUNT(*) as total,
                    SUM(montant) as montant_total,
                    SUM(CASE WHEN statut = 'valide' THEN 1 ELSE 0 END) as valides,
                    SUM(CASE WHEN statut = 'emis' THEN 1 ELSE 0 END) as emis
                ")->first();

            // CHÈQUES
            $chequesStats = Cheque::whereBetween('date_recouvrement', [$from, $to])
                ->selectRaw("
                    COUNT(*) as total,
                    SUM(montant) as montant_total,
                    COUNT(DISTINCT client_id) as clients_uniques
                ")->first();

            return [
                'currentBalance' => $currentBalance,
                'movementsToday' => $movementsToday,
                'movementsPeriod' => $movementsPeriod,
                
                'entrees' => $entrees,
                'sorties' => $sorties,
                'fluxNet' => $fluxNet,
                
                'byType' => $byType,
                
                'contreBonsTotal' => $contreBonsStats->total ?? 0,
                'contreBonsMontant' => $contreBonsStats->montant_total ?? 0,
                'contreBonsEcart' => $contreBonsStats->ecart_total ?? 0,
                
                'bonsRecouvrementTotal' => $bonsRecouvrement->total ?? 0,
                'bonsRecouvrementMontant' => $bonsRecouvrement->montant_total ?? 0,
                'clientsRecouvres' => $bonsRecouvrement->clients_uniques ?? 0,
                
                'depensesTotal' => $depensesStats->total ?? 0,
                'depensesMontant' => $depensesStats->montant_total ?? 0,
                
                'transfertsTotal' => $transfertsStats->total ?? 0,
                'transfertsMontant' => $transfertsStats->montant_total ?? 0,
                'transfertsValides' => $transfertsStats->valides ?? 0,
                
                'chequesTotal' => $chequesStats->total ?? 0,
                'chequesMontant' => $chequesStats->montant_total ?? 0,
                'chequesClients' => $chequesStats->clients_uniques ?? 0,
            ];
        });
    }

    private function getChartsData($cashId, $from, $to)
    {
        if (!$cashId) {
            return ['dailyData' => [], 'typeData' => []];
        }

        // Données quotidiennes pour le graphique
        $dailyData = CashMovement::where('cash_id', $cashId)
            ->whereBetween('date_mvt', [$from, $to])
            ->selectRaw("
                DATE(date_mvt) as date,
                SUM(CASE WHEN type IN ('recette','transfert_credit') THEN montant ELSE 0 END) as entrées,
                SUM(CASE WHEN type IN ('depense','transfert_debit') THEN montant ELSE 0 END) as sorties
            ")
            ->groupBy(DB::raw('DATE(date_mvt)'))
            ->orderBy('date')
            ->get();

        // Données par type pour le camembert
        $typeData = CashMovement::where('cash_id', $cashId)
            ->whereBetween('date_mvt', [$from, $to])
            ->selectRaw("
                type,
                SUM(montant) as montant
            ")
            ->groupBy('type')
            ->get();

        return [
            'dailyData' => $dailyData,
            'typeData' => $typeData,
        ];
    }

    private function getRecentActivities($cashId)
    {
        if (!$cashId) {
            return ['recentMovements' => [], 'alerts' => []];
        }

        $recentMovements = CashMovement::with('source')
            ->where('cash_id', $cashId)
            ->orderByDesc('date_mvt')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        // Alertes simplifiées (sans champ closed)
        $alerts = $this->getAlerts($cashId);

        return [
            'recentMovements' => $recentMovements,
            'alerts' => $alerts,
        ];
    }

    private function getAlerts($cashId)
    {
        $alerts = [];

        // Contre-bons avec écart important (> 10%) - SANS champ closed
        $contreBonsAlerte = ContreBon::whereRaw('ABS(ecart/montant) > 0.1')
            ->get();
        
        if ($contreBonsAlerte->count() > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => $contreBonsAlerte->count() . ' contre-bon(s) avec écart important',
                'link' => route('contre-bons.index')
            ];
        }

        // Transferts en attente
        $transfertsEnAttente = Transfer::where('from_cash_id', $cashId)
            ->where('statut', 'emis')
            ->count();
        
        if ($transfertsEnAttente > 0) {
            $alerts[] = [
                'type' => 'info',
                'message' => $transfertsEnAttente . ' transfert(s) en attente de validation',
                'link' => route('transfers.index')
            ];
        }

        return $alerts;
    }

    private function getEmptyStats()
    {
        return [
            'currentBalance' => 0,
            'movementsToday' => 0,
            'movementsPeriod' => 0,
            'entrees' => 0,
            'sorties' => 0,
            'fluxNet' => 0,
            'byType' => collect(),
            'contreBonsTotal' => 0,
            'contreBonsMontant' => 0,
            'contreBonsEcart' => 0,
            'bonsRecouvrementTotal' => 0,
            'bonsRecouvrementMontant' => 0,
            'clientsRecouvres' => 0,
            'depensesTotal' => 0,
            'depensesMontant' => 0,
            'transfertsTotal' => 0,
            'transfertsMontant' => 0,
            'transfertsValides' => 0,
            'chequesTotal' => 0,
            'chequesMontant' => 0,
            'chequesClients' => 0,
        ];
    }
}
