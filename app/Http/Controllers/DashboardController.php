<?php

namespace App\Http\Controllers;

use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\Cheque;
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

        // Période sélectionnée (par défaut: mois en cours)
        $from = $request->filled('from') ? Carbon::parse($request->get('from'))->startOfDay() : Carbon::now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->get('to'))->endOfDay() : Carbon::now()->endOfDay();

        $currentBalance = null;
        $movementsCount = null;
        $lastMovements = [];
        $totalsIn = 0.0;
        $totalsOut = 0.0;
        $netFlow = 0.0;
        $byType = collect();

        // Série journalière (dates, entrées, sorties)
        $dailyDates = [];
        $dailyIn = [];
        $dailyOut = [];

        // Stats chèques (période)
        $chequesCount = 0;
        $chequesSum = 0.0;

        // Clients recouvrés (unique) via Bons + Chèques (période)
        $clientsRecouvres = 0;

        if ($cashId) {
            // Solde courant: dernier balance connu pour cette caisse
            $currentBalance = DB::table('cash_movements')
                ->where('cash_id', $cashId)
                ->orderByDesc('date_mvt')
                ->orderByDesc('id')
                ->value('balance');

            // Nb mouvements sur les dernières 24h
            $movementsCount = CashMovement::where('cash_id', $cashId)
                ->where('date_mvt', '>=', Carbon::now()->subDay())
                ->count();

            // Derniers mouvements (10)
            $lastMovements = CashMovement::with('source')
                ->where('cash_id', $cashId)
                ->orderByDesc('date_mvt')
                ->orderByDesc('id')
                ->limit(10)
                ->get();

            // Agrégats période
            $base = CashMovement::where('cash_id', $cashId)
                ->whereBetween('date_mvt', [$from, $to]);

            $totalsIn = (clone $base)->whereIn('type', ['recette','transfert_credit'])->sum('montant');
            $totalsOut = (clone $base)->whereIn('type', ['depense','transfert_debit','ajustement'])->sum('montant');
            $netFlow = round((float)$totalsIn - (float)$totalsOut, 2);

            $byType = (clone $base)
                ->select('type', DB::raw('COUNT(*) as cnt'), DB::raw('SUM(montant) as total'))
                ->groupBy('type')
                ->orderBy('type')
                ->get();

            // Série journalière entrées/sorties
            $rows = (clone $base)
                ->select(
                    DB::raw("DATE(date_mvt) as d"),
                    DB::raw("SUM(CASE WHEN type IN ('recette','transfert_credit') THEN montant ELSE 0 END) as total_in"),
                    DB::raw("SUM(CASE WHEN type IN ('depense','transfert_debit','ajustement') THEN montant ELSE 0 END) as total_out")
                )
                ->groupBy(DB::raw('DATE(date_mvt)'))
                ->orderBy(DB::raw('DATE(date_mvt)'))
                ->get();

            // Remplir les jours manquants de la période
            $cursor = $from->copy()->startOfDay();
            $map = $rows->keyBy('d');
            while ($cursor->lte($to)) {
                $key = $cursor->toDateString();
                $dailyDates[] = $key;
                $dailyIn[] = isset($map[$key]) ? (float)$map[$key]->total_in : 0.0;
                $dailyOut[] = isset($map[$key]) ? (float)$map[$key]->total_out : 0.0;
                $cursor->addDay();
            }

            // Chèques (période) – basés sur date_recouvrement
            $chequesQuery = Cheque::query()
                ->whereNotNull('date_recouvrement')
                ->whereBetween('date_recouvrement', [$from->toDateString(), $to->toDateString()]);
            $chequesCount = (clone $chequesQuery)->count();
            $chequesSum = (clone $chequesQuery)->sum('montant');

            // Clients recouvrés via bons de recouvrement (date_recouvrement) ou chèques (date_recouvrement)
            $clientsFromBons = RecouvrementBon::query()
                ->whereNotNull('date_recouvrement')
                ->whereBetween('date_recouvrement', [$from->toDateString(), $to->toDateString()])
                ->whereNotNull('client_id')
                ->pluck('client_id')
                ->unique();

            $clientsFromCheques = Cheque::query()
                ->whereNotNull('date_recouvrement')
                ->whereBetween('date_recouvrement', [$from->toDateString(), $to->toDateString()])
                ->whereNotNull('client_id')
                ->pluck('client_id')
                ->unique();

            $clientsRecouvres = $clientsFromBons->merge($clientsFromCheques)->unique()->count();
        }

        return view('dashboard', [
            'currentBalance' => $currentBalance,
            'movementsCount' => $movementsCount,
            'lastMovements' => $lastMovements,
            'periodFrom' => $from->format('Y-m-d'),
            'periodTo' => $to->format('Y-m-d'),
            'totalsIn' => $totalsIn,
            'totalsOut' => $totalsOut,
            'netFlow' => $netFlow,
            'byType' => $byType,
            // Graph
            'dailyDates' => $dailyDates,
            'dailyIn' => $dailyIn,
            'dailyOut' => $dailyOut,
            // Cheques & clients recouvrés
            'chequesCount' => $chequesCount,
            'chequesSum' => $chequesSum,
            'clientsRecouvres' => $clientsRecouvres,
        ]);
    }
}