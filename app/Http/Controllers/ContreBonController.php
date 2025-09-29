<?php

namespace App\Http\Controllers;

use App\Models\ContreBon;
use App\Models\RecouvrementBon;
use App\Models\Company;
use App\Models\User;
use App\Models\CashMovement;
use App\Models\Client;
use App\Models\CashRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContreBonController extends Controller
{
    public function index()
    {
        $contreBons = ContreBon::latest('date')->paginate(20);
        return view('contre_bons.index', compact('contreBons'));
    }

    public function create()
    {
        $companies = Company::orderBy('name')->get();
        $livreurs = User::orderBy('name')->get();
        // Utilise la vue deposit comme écran de création d'un nouveau contre-bon
        return view('contre_bons.deposit', compact('companies','livreurs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero' => 'required|string',
            'company_id' => 'required|exists:companies,id',
            'livreur_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'montant' => 'required|numeric|min:0',
            'note' => 'nullable|string'
        ]);

        // Crée un contre-bon (brouillon), montant saisi pour calcul d'écart
        $contreBon = ContreBon::create([
            'numero' => $validated['numero'],
            'company_id' => $validated['company_id'],
            'livreur_id' => $validated['livreur_id'],
            'date' => $validated['date'],
            'montant' => (float) $validated['montant'], // montant attendu saisi
            'nombre_bons' => 0,
            'ecart' => 0, // recalculé quand des lignes sont ajoutées
            'note' => $validated['note'] ?? null,
        ]);

        return redirect()->route('contre-bons.show', $contreBon)->with('ok', 'Contre‑bon créé. Ajoutez maintenant des bons.');
    }

    public function edit(ContreBon $contreBon)
    {
        // Redirige vers show qui fait office d'écran d'édition
        return redirect()->route('contre-bons.show', $contreBon);
    }

    public function update(Request $request, ContreBon $contreBon)
    {
        $this->ensureNotValidated($contreBon);
        $data = $request->validate([
            'numero' => 'required|string',
            'date' => 'required|date',
            'montant' => 'required|numeric|min:0',
            'note' => 'nullable|string'
        ]);
        $contreBon->fill($data)->save();
        // Recalcule l'écart après modification du montant saisi
        $this->refreshTotals($contreBon);
        return back()->with('ok', 'Contre‑bon mis à jour.');
    }

    public function destroy(ContreBon $contreBon)
    {
        $this->ensureNotValidated($contreBon);
        DB::transaction(function () use ($contreBon) {
            RecouvrementBon::where('contre_bon_id', $contreBon->id)->update(['contre_bon_id' => null]);
            $contreBon->delete();
        });
        return redirect()->route('contre-bons.index')->with('ok', 'Contre‑bon supprimé.');
    }

    public function addBon(Request $request, ContreBon $contreBon)
    {
        $this->ensureNotValidated($contreBon);
        $data = $request->validate([
            'numero' => 'required|string',
            'client_id' => 'required|exists:clients,id',
            'montant' => 'required|numeric|min:0.01',
            'type' => 'required|in:espece,cheque',
            'note' => 'nullable|string',
        ]);
        $data['company_id'] = $contreBon->company_id;
        $data['livreur_id'] = $contreBon->livreur_id;
        $data['date_recouvrement'] = $contreBon->date;
        $data['contre_bon_id'] = $contreBon->id;
        RecouvrementBon::create($data);
        $this->refreshTotals($contreBon);
        return back()->with('ok', 'Ligne ajoutée.');
    }

    public function removeBon(ContreBon $contreBon, RecouvrementBon $bon)
    {
        $this->ensureNotValidated($contreBon);
        abort_unless($bon->contre_bon_id === $contreBon->id, 404);
        $bon->delete();
        $this->refreshTotals($contreBon);
        return back()->with('ok', 'Ligne supprimée.');
    }

    public function validateContreBon(Request $request, ContreBon $contreBon)
    {
        $this->ensureNotValidated($contreBon);
        $cashId = Auth::user()?->current_cash_id;
        if (!$cashId) {
            return back()->withErrors(['cash' => "Aucune caisse courante sélectionnée."]);
        }
        // Doit contenir au moins une ligne
        $count = RecouvrementBon::where('contre_bon_id', $contreBon->id)->count();
        if ($count === 0) {
            return back()->withErrors(['validation' => "Le contre‑bon doit contenir au moins un bon de recouvrement."]);
        }

        return DB::transaction(function () use ($cashId, $contreBon) {
            // Calcul total espèces (chèques n'impactent pas la caisse ici)
            $totalEspeces = RecouvrementBon::where('contre_bon_id', $contreBon->id)
                ->where('type', 'espece')
                ->sum('montant');

            if ($totalEspeces > 0) {
                CashMovement::create([
                    'cash_id' => $cashId,
                    'type' => 'recette',
                    'montant' => $totalEspeces,
                    'source_type' => ContreBon::class,
                    'source_id' => $contreBon->id,
                    'description' => trim(($contreBon->livreur?->name ?? '') . ' ' . ($contreBon->company?->code ?? '')),
                    'date_mvt' => Carbon::parse($contreBon->date),
                ]);
            }

            // Marque validé
            $contreBon->validated_at = now();
            $contreBon->validated_by = Auth::id();
            $contreBon->validated_cash_id = $cashId;
            $contreBon->save();

          //  return redirect()->route('contre-bons.show', $contreBon)->with('ok', 'Contre‑bon validé et mouvement enregistré.');
        return redirect()->route('movements.index')->with('ok', 'Contre‑bon validé et mouvement enregistré.');    
        });
    }

    public function show(ContreBon $contreBon)
    {
        $bons = RecouvrementBon::where('contre_bon_id', $contreBon->id)->get();
        $clients = Client::orderBy('name')->get();
        $caisses = CashRegister::orderBy('name')->get();
        return view('contre_bons.show', compact('contreBon','bons','clients','caisses'));
    }

    private function refreshTotals(ContreBon $contreBon): void
    {
        $bons = RecouvrementBon::where('contre_bon_id', $contreBon->id)->get();
        $totalBons = (float) $bons->sum('montant');
        $contreBon->nombre_bons = (int) $bons->count();
        // Ne pas écraser le montant saisi sur l'entête; calculer l'écart = montant saisi - somme des lignes
        // montant (header) = attendu, totalBons = constaté
        $contreBon->ecart = (float) ($contreBon->montant - $totalBons);
        $contreBon->save();
    }

    private function ensureNotValidated(ContreBon $contreBon): void
    {
        abort_if(!is_null($contreBon->validated_at), 403, 'Contre‑bon déjà validé.');
    }

    // Suggestion du prochain numéro selon société/livreur/année
    public function suggestNumero(Request $request)
    {
        $request->validate([
            'company_id' => 'required|integer',
            'livreur_id' => 'required|integer',
            'date' => 'required|date',
        ]);
        $year = date('Y', strtotime($request->input('date')));
        $last = ContreBon::where('company_id', $request->input('company_id'))
            ->where('livreur_id', $request->input('livreur_id'))
            ->whereYear('date', $year)
            ->latest('id')
            ->first();
        $next = '1';
        if ($last && $last->numero) {
            if (preg_match('/(\d+)(?!.*\d)/', $last->numero, $m)) {
                $next = (string)((int)$m[1] + 1);
            } else {
                $next = $last->numero; // fallback
            }
        }
        return response()->json(['suggestion' => $next]);
    }
}