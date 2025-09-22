<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessImportJob;
use App\Models\Import;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ImportController extends Controller
{
    public function index()
    {
        $imports = Import::latest()->paginate(20);
        return view('imports.index', compact('imports'));
    }

    public function create()
    {
        $entities = [
            'clients' => 'Clients',
            'cheques' => 'Chèques',
            'recouvrement_bons' => 'Bons de recouvrement',
            'contre_bons' => 'Contre-bons',
            'expenses' => 'Dépenses',
            'transfers' => 'Transferts',
            'cash_movements' => 'Mouvements de caisse',
        ];
        return view('imports.create', compact('entities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'entity' => ['required', Rule::in(['clients','cheques','recouvrement_bons','contre_bons','expenses','transfers','cash_movements'])],
            'file' => ['required','file','mimes:csv,txt,xlsx']
        ]);

        $path = $request->file('file')->store('imports');

        $import = Import::create([
            'entity' => $validated['entity'],
            'file_path' => $path,
            'created_by' => Auth::id(),
            'status' => 'pending',
        ]);

        ProcessImportJob::dispatch($import);

        return redirect()->route('imports.index')->with('status', 'Import démarré. Vous pouvez suivre sa progression.');
    }

    public function show(Import $import)
    {
        return view('imports.show', compact('import'));
    }

    public function template(Request $request)
    {
        $entity = $request->query('entity');
        $headers = match ($entity) {
            'clients' => ['name','code'],
            'cheques' => ['date_recouvrement','code_banque','numero','client_id','client_name','company_id','company','livreur_id','livreur','livreur_email','montant','echeance','statut'],
            'recouvrement_bons' => ['numero','date_recouvrement','client_code','client_name','company_id','company','livreur_id','livreur','livreur_email','montant','type','note','contre_bon_numero'],
            'contre_bons' => ['numero','date','company_id','company','livreur_id','livreur','livreur_email','montant','nombre_bons','note'],
            'expenses' => ['cash_id','cash_name','date','numero','libelle','montant','note'],
            'transfers' => ['numero','from_cash_id','from_cash_name','to_cash_id','to_cash_name','montant','note'],
            'cash_movements' => ['cash_id','cash_name','type','montant','date_mvt','description','source_type','source_id'],
            default => null,
        };

        if (!$headers) {
            abort(400, 'Entité invalide.');
        }

        $csv = implode(',', $headers) . "\n"; // header only
        $filename = $entity . '_template.csv';
        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }
}