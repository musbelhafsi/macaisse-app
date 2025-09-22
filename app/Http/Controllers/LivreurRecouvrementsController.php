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
        
        $type = $request->get('type'); // 'espèce' ou 'chèque'

    if ($type === 'espece') {
        // 👉 Uniquement recouvrements en espèces
        $recouvrements = RecouvrementBon::with(['client','company'])
            ->where('livreur_id', $user->id)
            ->when($request->filled('from') && $request->filled('to'),
                fn($q) => $q->whereBetween('date_recouvrement', [$request->from, $request->to]))
            ->get()
            ->each(fn($bon) => $bon->type = 'espèce');

    } elseif ($type === 'cheque') {
        // 👉 Uniquement recouvrements par chèque
        $recouvrements = Cheque::with(['client','company'])
            ->where('livreur_id', $user->id)
            ->when($request->filled('from') && $request->filled('to'),
                fn($q) => $q->whereBetween('date_recouvrement', [$request->from, $request->to]))
            ->get()
            ->each(fn($cheque) => $cheque->type = 'chèque');

    } else {

        // Recouvrements en espèces
    $bons = RecouvrementBon::with(['client','company'])
        ->where('livreur_id', $user->id)
       // ->when($request->filled('type'), fn($q) => $q->where('type', $request->type))
        ->when($request->filled('from') && $request->filled('to'),
            fn($q) => $q->whereBetween('date_recouvrement', [$request->from, $request->to]))
        ->get()
         ->each(fn($bon) => $bon->type = 'espèce');

        // Recouvrements par chèque
    $cheques = Cheque::with(['client','company'])
        ->where('livreur_id', $user->id)
        ->when($request->filled('from') && $request->filled('to'),
            fn($q) => $q->whereBetween('date_recouvrement', [$request->from, $request->to]))
        ->get()
        ->each(fn($cheque) => $cheque->type = 'chèque');

    // Fusionner les deux collections
     $recouvrements = $bons->merge($cheques);
    }
    // Trier par date de recouvrement décroissante
    $allRecouvrements = $recouvrements->sortByDesc('date_recouvrement')->values();   
        // Pagination manuelle
        $perPage = 20;
        $currentPage = $request->input('page', 1);
        $currentItems = $allRecouvrements->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $allRecouvrements = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $allRecouvrements->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );  
      //  dd($allRecouvrements->toArray());


    return view('livreurs.recouvrements', compact('allRecouvrements')); 
    }
}