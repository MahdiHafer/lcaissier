<?php

namespace App\Http\Controllers;

use App\BonLivraison;
use Illuminate\Http\Request;
use App\Client;
use App\Facture;
use App\Vente;


class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
{
    $query = Client::query();

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('nom', 'like', "%$search%")
              ->orWhere('telephone', 'like', "%$search%")
              ->orWhere('email', 'like', "%$search%");
        });
    }

    $clients = $query->latest()->get();

    return view('clients.index', compact('clients'));
}


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
{
    return view('clients.create');
}


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
public function store(Request $request)
{
    $validated = $request->validate([
        'nom'       => 'required|string|max:255',
        'societe'   => 'nullable|string|max:255',
        'ice'       => 'nullable|string|max:255',
        'rc'        => 'nullable|string|max:255',
        'if_fiscal' => 'nullable|string|max:255',
        'telephone' => 'nullable|string|max:50',
        'email'     => 'nullable|email|max:255',
        'adresse'   => 'nullable|string|max:255',
    ]);

    Client::create($validated);

    return redirect()->route('clients.index')->with('success', 'Client ajouté avec succès.');
}

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Client $client)
    {
        $ventes = Vente::with('details')
            ->where('client', $client->id)
            ->latest()
            ->get();

        $bonsLivraison = BonLivraison::with('details')
            ->where('client_id', $client->id)
            ->latest()
            ->get();

        $factures = Facture::with('details')
            ->where('client_id', $client->id)
            ->latest()
            ->get();

        $caTotal = (float) $ventes->sum('net_a_payer');
        $totalPaye = (float) $ventes->sum('montant_paye');
        $totalCredit = (float) $ventes->reduce(function ($carry, $vente) {
            return $carry + max((float) $vente->net_a_payer - (float) $vente->montant_paye, 0);
        }, 0);
        $totalBl = (float) $bonsLivraison->sum('total');
        $totalFactures = (float) $factures->sum('total_ttc');

        $mouvements = collect();

        foreach ($ventes as $vente) {
            $mouvements->push([
                'date' => $vente->created_at,
                'type' => 'Vente',
                'numero' => $vente->numero_ticket,
                'montant' => (float) $vente->net_a_payer,
                'paye' => (float) $vente->montant_paye,
                'reste' => max((float) $vente->net_a_payer - (float) $vente->montant_paye, 0),
                'status' => ((float) $vente->net_a_payer - (float) $vente->montant_paye) > 0 ? 'Credit' : 'Solde',
            ]);
        }

        foreach ($bonsLivraison as $bon) {
            $mouvements->push([
                'date' => $bon->created_at,
                'type' => 'BL',
                'numero' => $bon->numero,
                'montant' => (float) $bon->total,
                'paye' => null,
                'reste' => null,
                'status' => ucfirst($bon->status ?? 'brouillon'),
            ]);
        }

        foreach ($factures as $facture) {
            $mouvements->push([
                'date' => $facture->created_at,
                'type' => 'Facture',
                'numero' => $facture->numero,
                'montant' => (float) $facture->total_ttc,
                'paye' => null,
                'reste' => null,
                'status' => 'Emise',
            ]);
        }

        $mouvements = $mouvements->sortByDesc('date')->values();

        return view('clients.show', compact(
            'client',
            'ventes',
            'bonsLivraison',
            'factures',
            'mouvements',
            'caTotal',
            'totalPaye',
            'totalCredit',
            'totalBl',
            'totalFactures'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
public function edit(Client $client)
{
    return view('clients.edit', compact('client'));
}


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
public function update(Request $request, Client $client)
{
    $validated = $request->validate([
        'nom'       => 'required|string|max:255',
        'societe'   => 'nullable|string|max:255',
        'ice'       => 'nullable|string|max:255',
        'rc'        => 'nullable|string|max:255',
        'if_fiscal' => 'nullable|string|max:255',
        'telephone' => 'nullable|string|max:50',
        'email'     => 'nullable|email|max:255',
        'adresse'   => 'nullable|string|max:255',
    ]);

    $client->update($validated);

    return redirect()->route('clients.index')->with('success', 'Client mis à jour avec succès.');
}


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
public function destroy(Client $client)
{
    $client->delete();

    return redirect()->route('clients.index')->with('success', 'Client supprimé avec succès.');
}

}
