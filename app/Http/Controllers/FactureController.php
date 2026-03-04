<?php

namespace App\Http\Controllers;

use App\Client;
use App\Facture;
use Illuminate\Http\Request;

class FactureController extends Controller
{
    public function index(Request $request)
    {
        $query = Facture::with('client')->latest();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('numero', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($qc) use ($search) {
                        $qc->where('nom', 'like', "%{$search}%")
                            ->orWhere('telephone', 'like', "%{$search}%");
                    });
            });
        }

        $factures = $query->paginate(20)->appends($request->all());
        return view('factures.index', compact('factures'));
    }

    public function show(Facture $facture)
    {
        $facture->load('details', 'client');
        return view('factures.show', compact('facture'));
    }

    public function edit(Facture $facture)
    {
        $clients = Client::orderBy('nom')->get();
        $facture->load('details');
        return view('factures.edit', compact('facture', 'clients'));
    }

    public function update(Request $request, Facture $facture)
    {
        $data = $request->validate([
            'date_facture' => 'required|date',
            'client_id' => 'nullable|exists:clients,id',
            'tva_rate' => 'required|numeric|min:0|max:100',
            'legal_company_name' => 'nullable|string|max:255',
            'legal_ice' => 'nullable|string|max:255',
            'legal_rc' => 'nullable|string|max:255',
            'legal_if' => 'nullable|string|max:255',
            'legal_cnss' => 'nullable|string|max:255',
            'legal_address' => 'nullable|string|max:255',
            'legal_phone' => 'nullable|string|max:255',
            'legal_email' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $totalHt = (float) $facture->details()->sum('total_ligne');
        $tvaRate = (float) $data['tva_rate'];
        $tvaAmount = round($totalHt * ($tvaRate / 100), 2);
        $totalTtc = round($totalHt + $tvaAmount, 2);

        $facture->update(array_merge($data, [
            'total_ht' => $totalHt,
            'tva_amount' => $tvaAmount,
            'total_ttc' => $totalTtc,
        ]));

        return redirect()->route('factures.show', $facture)->with('success', 'Facture mise a jour avec succes.');
    }

    public function destroy(Facture $facture)
    {
        $facture->delete();
        return back()->with('success', 'Facture supprimee avec succes.');
    }

    public function print(Facture $facture)
    {
        $facture->load('details', 'client');
        return view('factures.print', compact('facture'));
    }
}