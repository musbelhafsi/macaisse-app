<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index() { $items = Company::orderBy('name')->paginate(20); return view('companies.index', compact('items')); }
    public function create() { return view('companies.create'); }
    public function store(Request $r) {
        $data = $r->validate(['name'=>'required|string','code'=>'required|string|unique:companies,code']);
        Company::create($data);
        return redirect()->route('companies.index')->with('ok','Société créée');
    }
    public function edit(Company $company) { return view('companies.edit', compact('company')); }
    public function update(Request $r, Company $company) {
        $data = $r->validate(['name'=>'required|string','code'=>'required|string|unique:companies,code,'.$company->id]);
        $company->update($data);
        return redirect()->route('companies.index')->with('ok','Mis à jour');
    }
    public function destroy(Company $company) { $company->delete(); return back()->with('ok','Supprimé'); }
}