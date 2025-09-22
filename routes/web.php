<?php
//use App\Http\Controllers;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\ImportController;

Route::middleware(['auth'])->group(function () {
    // Static route first to avoid conflict with resource show route (imports/{import})
    Route::get('imports/template', [ImportController::class, 'template'])->name('imports.template');

    Route::resource('imports', ImportController::class)->only([
        'index', 'create', 'store', 'show'
    ]);
});
Route::middleware(['auth'])->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
});


Route::get('/', function () {
    
        return view('welcome');
    
    
});



Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'require.current.cash','role:admin|caissier'])
    ->name('dashboard');
    

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Sélection de la caisse après login
    Route::get('select-cash', [\App\Http\Controllers\Auth\CashSelectionController::class, 'show'])->name('auth.select-cash');
    Route::post('select-cash', [\App\Http\Controllers\Auth\CashSelectionController::class, 'store'])->name('auth.select-cash.store');

    // Bons de recouvrement (lecture seule via index, création désactivée)
    Route::resource('bons', \App\Http\Controllers\RecouvrementBonController::class)->only(['index'])->middleware(['require.current.cash','role:caissier|admin']);

    // Ecrans livreur (lecture seule)
    Route::get('mes-recouvrements', [\App\Http\Controllers\LivreurRecouvrementsController::class, 'index'])->name('livreurs.recouvrements')->middleware(['require.current.cash','role:livreur']);

    // Contre-bons (CRUD + actions)
    Route::resource('contre-bons', \App\Http\Controllers\ContreBonController::class)->only(['index','create','store','show','edit','update','destroy'])->middleware(['require.current.cash','role:admin|caissier']);
    Route::get('contre-bons/suggest-numero', [\App\Http\Controllers\ContreBonController::class, 'suggestNumero'])->name('contre-bons.suggest-numero')->middleware(['require.current.cash','role:caissier|admin']);
    Route::post('contre-bons/{contreBon}/add-bon', [\App\Http\Controllers\ContreBonController::class, 'addBon'])->name('contre-bons.add-bon')->middleware(['require.current.cash','role:caissier|admin']);
    Route::delete('contre-bons/{contreBon}/remove-bon/{bon}', [\App\Http\Controllers\ContreBonController::class, 'removeBon'])->name('contre-bons.remove-bon')->middleware(['require.current.cash','role:caissier|admin']);
    Route::post('contre-bons/{contreBon}/validate', [\App\Http\Controllers\ContreBonController::class, 'validateContreBon'])->name('contre-bons.validate')->middleware(['require.current.cash','role:caissier|admin']);

    // Bordereau (ancienne vue liée à un contre-bon)
    Route::get('contre-bons/{contreBon}/bordereau', [\App\Http\Controllers\ContreBonBordereauController::class, 'show'])->name('contre-bons.bordereau')->middleware(['require.current.cash']);
    Route::get('contre-bons/{contreBon}/bordereau.pdf', [\App\Http\Controllers\BordereauPdfController::class, 'show'])->name('contre-bons.bordereau.pdf')->middleware(['require.current.cash']);

    // Bordereaux d'envoi (nouvelle ressource)
    Route::resource('bordereaux', \App\Http\Controllers\BordereauController::class)
    ->only(['index','create','store','show','destroy'])
    ->parameters(['bordereaux' => 'bordereau'])
    ->middleware(['require.current.cash','role:caissier|admin']);
    Route::get('bordereaux/{bordereau}/pdf', [\App\Http\Controllers\BordereauController::class, 'pdf'])->name('bordereaux.pdf')->middleware(['require.current.cash','role:caissier|admin']);
    Route::post('bordereaux/{bordereau}/email', [\App\Http\Controllers\BordereauController::class, 'email'])->name('bordereaux.email')->middleware(['require.current.cash','role:caissier|admin']);
    Route::get('api/bordereaux/suggest-numero', [\App\Http\Controllers\BordereauController::class, 'apiSuggestNumero'])->name('bordereaux.suggest-numero')->middleware(['require.current.cash','role:caissier|admin']);
    Route::get('api/search/contre-bons', [\App\Http\Controllers\BordereauController::class, 'apiSearchContreBons'])->name('api.search.contre-bons')->middleware(['require.current.cash','role:caissier|admin']);
    Route::get('api/search/cheques', [\App\Http\Controllers\BordereauController::class, 'apiSearchCheques'])->name('api.search.cheques')->middleware(['require.current.cash','role:caissier|admin']);

    // Chèques (portefeuille)
    Route::get('cheques', [\App\Http\Controllers\ChequeController::class, 'index'])->name('cheques.index')->middleware(['require.current.cash','role:caissier|admin']);
    Route::get('cheques/export', [\App\Http\Controllers\ChequeController::class, 'export'])->name('cheques.export')->middleware(['require.current.cash','role:caissier|admin']);
    Route::get('cheques/create', [\App\Http\Controllers\ChequeController::class, 'create'])->name('cheques.create')->middleware(['require.current.cash','role:caissier|admin']);
    Route::get('cheques/{cheque}/edit', [\App\Http\Controllers\ChequeController::class, 'edit'])->name('cheques.edit')->middleware(['require.current.cash','role:caissier|admin']);
    Route::post('cheques', [\App\Http\Controllers\ChequeController::class, 'store'])->name('cheques.store')->middleware(['require.current.cash','role:caissier|admin']);
    Route::put('cheques/{cheque}', [\App\Http\Controllers\ChequeController::class, 'update'])->name('cheques.update')->middleware(['require.current.cash','role:caissier|admin']);
    Route::get('cheques/{cheque}', [\App\Http\Controllers\ChequeController::class, 'show'])->name('cheques.show')->middleware(['require.current.cash','role:caissier|admin']);
    Route::delete('cheques/{cheque}', [\App\Http\Controllers\ChequeController::class, 'destroy'])->name('cheques.destroy')->middleware(['require.current.cash','role:caissier|admin']);
    
    // Sociétés (admin)
    Route::resource('companies', \App\Http\Controllers\CompanyController::class)->middleware(['require.current.cash','role:admin']);
    // Caisses (responsable caisse)
    Route::resource('cash-registers', \App\Http\Controllers\CashRegisterController::class)->middleware(['require.current.cash','role:responsable_caisse|admin|caissier']);
    Route::post('cash-registers/{cash_register}/attach-user', [\App\Http\Controllers\CashRegisterController::class, 'attachUser'])->name('cash-registers.attach-user')->middleware(['require.current.cash','role:responsable_caisse|admin|caissier']);
    Route::delete('cash-registers/{cash_register}/detach-user/{user}', [\App\Http\Controllers\CashRegisterController::class, 'detachUser'])->name('cash-registers.detach-user')->middleware(['require.current.cash','role:responsable_caisse|admin|caissier']);
    // Clients (caissier)
    Route::resource('clients', \App\Http\Controllers\ClientController::class)->middleware(['require.current.cash','role:caissier|admin']);
    // Livreurs (admin)
    Route::resource('livreurs', \App\Http\Controllers\LivreurController::class)->middleware(['require.current.cash','role:admin|caissier']);
    // Mouvements (lecture seule)
    Route::get('movements', [\App\Http\Controllers\CashMovementController::class, 'index'])->name('movements.index')->middleware(['require.current.cash']);

    // Transferts de caisse
    Route::middleware(['require.current.cash'])->group(function(){
        Route::get('transfers', [\App\Http\Controllers\TransferController::class, 'index'])->name('transfers.index');
        Route::get('transfers/create', [\App\Http\Controllers\TransferController::class, 'create'])->name('transfers.create')->middleware('role:caissier|admin');
        Route::post('transfers', [\App\Http\Controllers\TransferController::class, 'store'])->name('transfers.store')->middleware('role:caissier|admin');
        Route::post('transfers/{transfer}/validate', [\App\Http\Controllers\TransferController::class, 'validateReception'])->name('transfers.validate')->middleware('role:responsable_caisse|caissier');
    });

    // Dépenses
    Route::get('expenses', [\App\Http\Controllers\ExpenseController::class, 'index'])->name('expenses.index')->middleware(['require.current.cash']);
    Route::get('expenses/create', [\App\Http\Controllers\ExpenseController::class, 'create'])->name('expenses.create')->middleware(['require.current.cash','role:caissier|admin']);
    Route::post('expenses', [\App\Http\Controllers\ExpenseController::class, 'store'])->name('expenses.store')->middleware(['require.current.cash','role:caissier|admin']);
});

require __DIR__.'/auth.php';
