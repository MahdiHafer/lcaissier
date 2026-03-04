<?php

use Illuminate\Support\Facades\Route;

// Route d'autorisation d'un appareil
Route::get('/autoriser', function () {
    if (request('cle') !== 'NuxeraGroup@2024') {
        abort(403, 'Code secret invalide.');
    }

    $token = bin2hex(random_bytes(32));
    \App\AuthorizedDevice::create([
        'token' => $token,
        'device_name' => request()->header('User-Agent'),
    ]);

    return response('Appareil autorise')->cookie(
        'magasin_appareil_autorise',
        $token,
        60 * 24 * 365 * 5
    );
});

Auth::routes(['register' => false]);

Route::middleware(['auth'])->group(function () {
    Route::resource('fournisseurs', 'FournisseurController');
    Route::resource('clients', 'ClientController');

    Route::get('/', 'VenteController@dashboard')->name('dashboard.index');
    Route::get('/dashboard', 'VenteController@dashboard')->name('dashboard.index');

    Route::get('/caisse', 'VenteController@index')->name('caisse.index');
    Route::get('/caisse/imprimer', 'VenteController@imprimerTicket')->name('caisse.imprimer');
    Route::post('/caisse/store-infos-ticket', 'VenteController@storeInfosTicket')->name('caisse.storeInfosTicket');
    Route::post('/caisse/add', 'VenteController@addToCart')->name('caisse.add');
    Route::post('/caisse/valider', 'VenteController@validerVente')->name('caisse.valider');
    Route::delete('/caisse/remove/{id}', 'VenteController@remove')->name('caisse.remove');
    Route::post('/caisse/vider', 'VenteController@vider')->name('caisse.vider');
    Route::post('/caisse/toggle-retour/{key}', 'VenteController@toggleRetour')->name('caisse.toggleRetour');

    Route::get('/ventes/historique', 'VenteController@historique')->name('ventes.historique');
    Route::delete('/ventes/{id}', 'VenteController@destroy')->name('ventes.destroy');
    Route::post('/ventes/{vente}/payer-credit', 'VenteController@payerCredit')->name('ventes.payer_credit');
    Route::get('/ventes/{vente}/avoir', 'AvoirController@createFromSale')->name('ventes.avoir.create');
    Route::post('/ventes/{vente}/avoir', 'AvoirController@storeFromSale')->name('ventes.avoir.store');
    Route::get('/ventes/{id}/edit', 'VenteController@editvente')->name('ventes.edit');
    Route::post('/ventes/{id}/update-simple', 'VenteController@updatevente')->name('ventes.update.simple');

    Route::get('/stock', 'StockController@dashboard')->name('stock.dashboard');
    Route::get('/stock/movements', 'StockController@movements')->name('stock.movements');
    Route::get('/inventory', 'InventoryController@index')->name('inventory.index');
    Route::get('/inventory/create', 'InventoryController@create')->name('inventory.create');
    Route::post('/inventory', 'InventoryController@store')->name('inventory.store');
    Route::get('/inventory/{inventory}', 'InventoryController@show')->name('inventory.show');
    Route::post('/inventory/{inventory}/save', 'InventoryController@saveCounts')->name('inventory.save');
    Route::post('/inventory/{inventory}/validate', 'InventoryController@validateInventory')->name('inventory.validate');

    Route::get('/check-codebar', function (Illuminate\Http\Request $request) {
        $exists = \App\Product::where('codebar', $request->codebar)->exists();
        return response()->json(['unique' => !$exists]);
    });

    Route::post('/products/export-bon', 'ProductController@exportBon')->name('products.exportBon');
    Route::post('/products/recalculate-stock', 'ProductController@recalculateGlobalStock')->name('products.recalculateStock');
    Route::get('/products/reference-next', 'ProductController@nextReference')->name('products.referenceNext');
    Route::resource('products', 'ProductController');
    Route::get('/products/{product}/print-label', 'ProductController@printLabel')->name('products.printLabel');

    Route::resource('users', 'UtilisateurController');
    Route::resource('categories', 'CategoryController')->except(['create', 'show', 'edit']);
    Route::resource('colors', 'ColorController')->except(['create', 'show', 'edit']);

    Route::resource('bons-livraison', 'BonLivraisonController')->except(['show']);
    Route::get('/bons-livraison/{bon}/print', 'BonLivraisonController@print')->name('bons-livraison.print');
    Route::get('/bons-livraison/{bon}/convert', 'BonLivraisonController@convertForm')->name('bons-livraison.convert');
    Route::post('/bons-livraison/{bon}/convert', 'BonLivraisonController@convertToSale')->name('bons-livraison.convert.store');
    Route::resource('devis', 'DevisController')->except(['show']);
    Route::get('/devis/{devi}/print', 'DevisController@print')->name('devis.print');
    Route::get('/avoirs', 'AvoirController@index')->name('avoirs.index');
    Route::get('/avoirs/{avoir}/print', 'AvoirController@print')->name('avoirs.print');

    Route::resource('factures', 'FactureController')->except(['create', 'store']);
    Route::get('/factures/{facture}/print', 'FactureController@print')->name('factures.print');
});
