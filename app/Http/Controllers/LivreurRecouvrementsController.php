<?php

namespace App\Http\Controllers;

use App\Models\Cheque;
use App\Models\RecouvrementBon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LivreurRecouvrementsController extends Controller
{
    public function index(Request $request)
{
    $user = Auth::user();
    $type = $request->get('type');

    // Utiliser des query builders séparés au lieu de get() pour mieux paginer
    $bonsQuery = RecouvrementBon::with(['client','company'])
        ->where('livreur_id', $user->id);
    
    $chequesQuery = Cheque::with(['client','company'])
        ->where('livreur_id', $user->id);

    // Appliquer les filtres de date
    if ($request->filled('from') && $request->filled('to')) {
        $bonsQuery->whereBetween('date_recouvrement', [$request->from, $request->to]);
        $chequesQuery->whereBetween('date_recouvrement', [$request->from, $request->to]);
    }

    if ($type === 'espece') {
        $allRecouvrements = $bonsQuery->orderBy('date_recouvrement', 'desc')->paginate(20);
        $allRecouvrements->each(fn($bon) => $bon->type = 'espèce');
        
    } elseif ($type === 'cheque') {
        $allRecouvrements = $chequesQuery->orderBy('date_recouvrement', 'desc')->paginate(20);
        $allRecouvrements->each(fn($cheque) => $cheque->type = 'chèque');
        
    } else {
        // Pour "Tous", récupérer séparément et fusionner
        $bons = $bonsQuery->get()->each(fn($bon) => $bon->type = 'espèce');
        $cheques = $chequesQuery->get()->each(fn($cheque) => $cheque->type = 'chèque');
        
        $recouvrements = $bons->merge($cheques)->sortByDesc('date_recouvrement');
        
        // Pagination manuelle pour le cas "Tous"
        $perPage = 20;
        $currentPage = $request->input('page', 1);
        $currentItems = $recouvrements->slice(($currentPage - 1) * $perPage, $perPage);
        
        $allRecouvrements = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $recouvrements->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    return view('livreurs.recouvrements', compact('allRecouvrements'));
}

}