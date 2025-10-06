<?php

namespace App\Http\Controllers;

use App\Models\Bordereau;
use App\Models\BordereauLigne;
use App\Models\ContreBon;
use App\Models\Cheque;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class BordereauController extends Controller
{
    public function index()
    {
        $items = Bordereau::latest()->paginate(20);
        return view('bordereaux.index', compact('items'));
    }

    public function create()
    {
        // Vue type deposit: entête minimal + ajout de lignes (contre-bons et chèques)
        return view('bordereaux.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date_envoi' => 'required|date',
            'note' => 'nullable|string',
        ]);

        return DB::transaction(function() use ($data, $request) {
            $numero = $this->suggestNumero($data['date_envoi']);
            $bordereau = Bordereau::create([
                'numero' => $numero,
                'date_envoi' => $data['date_envoi'],
                'note' => $data['note'] ?? null,
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            // Lignes sélectionnées existantes (contre-bons)
            foreach ((array)$request->input('contre_bon_ids', []) as $id) {
                if (!$id) continue;
                $cb = ContreBon::find($id);
                if (!$cb) continue;
                BordereauLigne::create([
                    'bordereau_id' => $bordereau->id,
                    'type' => 'contre_bon',
                    'reference_id' => $cb->id,
                    'numero_ref' => $cb->numero,
                    'montant' => null, // impression seulement
                ]);
            }

            // Lignes chèques existants
            foreach ((array)$request->input('cheque_ids', []) as $id) {
                if (!$id) continue;
                $ch = Cheque::find($id);
                if (!$ch) continue;
                BordereauLigne::create([
                    'bordereau_id' => $bordereau->id,
                    'type' => 'cheque',
                    'reference_id' => $ch->id,
                    'numero_ref' => $ch->numero,
                    'montant' => $ch->montant,
                    'meta' => ['code_banque' => $ch->code_banque],
                ]);
            }

            // Chèques ad-hoc: pas de création dans la table cheques, on stocke seulement sur la ligne
            foreach ((array)$request->input('adhoc_cheques', []) as $row) {
                if (!is_array($row)) continue;
                $row = array_filter($row, fn($v) => $v !== null && $v !== '');
                if (!isset($row['numero']) || !isset($row['montant'])) continue;
                BordereauLigne::create([
                    'bordereau_id' => $bordereau->id,
                    'type' => 'cheque',
                    'reference_id' => null,
                    'numero_ref' => $row['numero'],
                    'montant' => $row['montant'],
                    'meta' => ['code_banque' => $row['code_banque'] ?? 'UNK', 'adhoc' => true],
                ]);
            }

            return redirect()->route('bordereaux.show', $bordereau)->with('ok', 'Bordereau créé.');
        });
    }

    public function show(Bordereau $bordereau)
    {
        $bordereau->load('lignes');
        // Récupération des entités pour affichage
        $contreBonIds = $bordereau->lignes->where('type','contre_bon')->pluck('reference_id');
        $chequeLignes = $bordereau->lignes->where('type','cheque');
        $chequeIds = $chequeLignes->pluck('reference_id')->filter();
        $contreBons = ContreBon::whereIn('id', $contreBonIds)->get();
        $cheques = Cheque::whereIn('id', $chequeIds)->get()->keyBy('id');
        return view('bordereaux.show', compact('bordereau','contreBons','cheques','chequeLignes'));
    }

    public function destroy(Bordereau $bordereau)
    {
        $bordereau->delete();
        return back()->with('ok', 'Bordereau supprimé.');
    }

    public function pdf(Bordereau $bordereau)
    {
        $bordereau->load('lignes');
        $contreBonIds = $bordereau->lignes->where('type','contre_bon')->pluck('reference_id');
        $chequeLignes = $bordereau->lignes->where('type','cheque');
        $chequeIds = $chequeLignes->pluck('reference_id')->filter();
        $contreBons = ContreBon::whereIn('id', $contreBonIds)->get();
        $cheques = Cheque::whereIn('id', $chequeIds)->get()->keyBy('id');
              
        
        $pdf = Pdf::loadView('bordereaux.pdf', compact('bordereau','contreBons','cheques','chequeLignes'))->setPaper('A4');
        return $pdf->download('bordereau_envoi_'.$bordereau->numero.'.pdf');
    }

   public function email(Request $request, Bordereau $bordereau)
{
    $data = $request->validate([
        'to' => 'required|email',
        'cc' => 'nullable|email',
        'subject' => 'nullable|string',
        'message' => 'nullable|string',
    ]);

    // Préparation des données
    $bordereau->load('lignes');
    $contreBonIds = $bordereau->lignes->where('type','contre_bon')->pluck('reference_id');
    $chequeLignes = $bordereau->lignes->where('type','cheque');
    $chequeIds = $chequeLignes->pluck('reference_id')->filter();
    $contreBons = ContreBon::whereIn('id', $contreBonIds)->get();
    $cheques = Cheque::whereIn('id', $chequeIds)->get()->keyBy('id');
    
    // Génération du PDF
    $pdf = Pdf::loadView('bordereaux.pdf', compact('bordereau','contreBons','cheques','chequeLignes'))->setPaper('A4');
    $pdfData = $pdf->output();

    // Message par défaut
    $messageText = $data['message'] ?? "Bonjour,\n\nVeuillez trouver ci-joint le bordereau d'envoi n°{$bordereau->numero}.\n\nCordialement.";

    // CORRECTION : Ajouter $pdfData dans le use()
    Mail::raw($messageText, function ($message) use ($data, $pdfData, $bordereau) {
        $message->to($data['to'])
                ->subject($data['subject'] ?? ('Bordereau d\'envoi ' . $bordereau->numero));
        
        if (!empty($data['cc'])) {
            $message->cc($data['cc']);
        }
        
        $message->attachData($pdfData, 'bordereau_envoi_' . $bordereau->numero . '.pdf', [
            'mime' => 'application/pdf'
        ]);
    });

    return back()->with('success', 'Email envoyé avec succès.');
}



    public function suggestNumero(string $date)
    {
        // Format: BE-YYYYMMDD-###
        $prefix = 'BE-'.date('Ymd', strtotime($date)).'-';
        $last = Bordereau::where('numero', 'like', $prefix.'%')->orderBy('numero','desc')->value('numero');
        $next = 1;
        if ($last) {
            $num = intval(substr($last, -3));
            $next = $num + 1;
        }
        return $prefix.str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    // Helpers API pour l'UI de création
    public function apiSuggestNumero(Request $request)
    {
        $date = $request->query('date');
        return response()->json(['suggestion' => $this->suggestNumero($date)]);
    }

    public function apiSearchContreBons(Request $request)
    {
        $q = $request->query('q');
        $cb = ContreBon::where('numero', $q)->first();
        if (!$cb) return response()->json(null);
        return response()->json(['id' => $cb->id, 'numero' => $cb->numero]);
    }

    public function apiSearchCheques(Request $request)
    {
        $q = $request->query('q');
        $ch = Cheque::where('numero', $q)->orWhere('id', $q)->first();
        if (!$ch) return response()->json(null);
        return response()->json(['id' => $ch->id, 'numero' => $ch->numero, 'montant' => $ch->montant, 'code_banque' => $ch->code_banque]);
    }
}