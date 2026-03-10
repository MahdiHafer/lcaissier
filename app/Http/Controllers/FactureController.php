<?php

namespace App\Http\Controllers;

use App\AppSetting;
use App\Client;
use App\Devis;
use App\Facture;
use App\Vente;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
        $facture->load('details', 'client', 'vente', 'devis', 'bonLivraison');
        return view('factures.show', compact('facture'));
    }

    public function edit(Facture $facture)
    {
        $clients = Client::orderBy('nom')->get();
        $facture->load('details');
        return view('factures.edit', compact('facture', 'clients'));
    }

    public function createFromVente(Vente $vente)
    {
        $vente->load('details', 'clientInfo');

        $existing = Facture::where('vente_id', $vente->id)->first();
        if ($existing) {
            return redirect()->route('factures.show', $existing)
                ->with('warning', 'Une facture existe deja pour cette vente.');
        }

        if (!$vente->details->count()) {
            return back()->with('error', 'Cette vente ne contient aucune ligne facturable.');
        }

        return view('factures.create_from_sale', compact('vente'));
    }

    public function storeFromVente(Request $request, Vente $vente)
    {
        $vente->load('details', 'clientInfo');

        if (Facture::where('vente_id', $vente->id)->exists()) {
            return back()->with('error', 'Une facture existe deja pour cette vente.');
        }

        $data = $this->validateFacturePayload($request);

        $baseTtc = round((float) $vente->details->sum('total_ligne'), 2);
        $totals = $this->computeTotalsWithHtDiscount(
            $baseTtc,
            (float) $data['tva_rate'],
            $data['remise_type'],
            (float) ($data['remise_value'] ?? 0)
        );
        $legal = $this->legalFieldsPayload();

        $facture = Facture::create(array_merge([
            'numero' => $this->generateFactureNumber(),
            'date_facture' => $data['date_facture'],
            'client_id' => $vente->client,
            'vente_id' => $vente->id,
            'remise_type' => $data['remise_type'],
            'remise_value' => (float) ($data['remise_value'] ?? 0),
            'remise_amount' => $totals['remise_ht'],
            'total_ht' => $totals['total_ht'],
            'tva_rate' => (float) $data['tva_rate'],
            'tva_amount' => $totals['tva_amount'],
            'total_ttc' => $totals['total_ttc'],
            'notes' => $data['notes'] ?? null,
            'user_id' => auth()->id(),
        ], $legal));

        foreach ($vente->details as $line) {
            $facture->details()->create([
                'product_id' => null,
                'designation' => $line->nom_produit,
                'quantite' => $line->quantite,
                'prix_unitaire' => $line->prix_unitaire,
                'total_ligne' => $line->total_ligne,
            ]);
        }

        return redirect()->route('factures.print', $facture)->with('success', 'Facture creee a partir de la vente avec succes.');
    }

    public function createFromDevis(Devis $devi)
    {
        $devis = $devi->load('details', 'client');

        $existing = Facture::where('devis_id', $devis->id)->first();
        if ($existing) {
            return redirect()->route('factures.show', $existing)
                ->with('warning', 'Une facture existe deja pour ce devis.');
        }

        if (!$devis->details->count()) {
            return back()->with('error', 'Ce devis ne contient aucune ligne facturable.');
        }

        return view('factures.create_from_devis', compact('devis'));
    }

    public function storeFromDevis(Request $request, Devis $devi)
    {
        $devis = $devi->load('details', 'client');

        if (Facture::where('devis_id', $devis->id)->exists()) {
            return back()->with('error', 'Une facture existe deja pour ce devis.');
        }

        $data = $this->validateFacturePayload($request);

        $baseTtc = round((float) $devis->details->sum('total_ligne'), 2);
        $totals = $this->computeTotalsWithHtDiscount(
            $baseTtc,
            (float) $data['tva_rate'],
            $data['remise_type'],
            (float) ($data['remise_value'] ?? 0)
        );
        $legal = $this->legalFieldsPayload();

        $facture = Facture::create(array_merge([
            'numero' => $this->generateFactureNumber(),
            'date_facture' => $data['date_facture'],
            'client_id' => $devis->client_id,
            'devis_id' => $devis->id,
            'remise_type' => $data['remise_type'],
            'remise_value' => (float) ($data['remise_value'] ?? 0),
            'remise_amount' => $totals['remise_ht'],
            'total_ht' => $totals['total_ht'],
            'tva_rate' => (float) $data['tva_rate'],
            'tva_amount' => $totals['tva_amount'],
            'total_ttc' => $totals['total_ttc'],
            'notes' => $data['notes'] ?? null,
            'user_id' => auth()->id(),
        ], $legal));

        foreach ($devis->details as $line) {
            $facture->details()->create([
                'product_id' => $line->product_id,
                'designation' => $line->designation,
                'quantite' => $line->quantite,
                'prix_unitaire' => $line->prix_unitaire,
                'total_ligne' => $line->total_ligne,
            ]);
        }

        return redirect()->route('factures.print', $facture)->with('success', 'Facture creee a partir du devis avec succes.');
    }

    public function update(Request $request, Facture $facture)
    {
        $data = $request->validate([
            'date_facture' => 'required|date',
            'client_id' => 'nullable|exists:clients,id',
            'tva_rate' => 'required|numeric|min:0|max:100',
            'remise_type' => 'required|in:dh,%',
            'remise_value' => 'nullable|numeric|min:0',
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

        if ($data['remise_type'] === '%' && (float) ($data['remise_value'] ?? 0) > 100) {
            throw ValidationException::withMessages([
                'remise_value' => 'La remise en pourcentage ne peut pas depasser 100%.',
            ]);
        }

        // Detail line prices are TTC: recompute HT/TVA by extraction, not by addition.
        $baseTtc = round((float) $facture->details()->sum('total_ligne'), 2);
        $totals = $this->computeTotalsWithHtDiscount(
            $baseTtc,
            (float) $data['tva_rate'],
            $data['remise_type'],
            (float) ($data['remise_value'] ?? 0)
        );

        $facture->update(array_merge($data, [
            'remise_value' => (float) ($data['remise_value'] ?? 0),
            'remise_amount' => $totals['remise_ht'],
            'total_ht' => $totals['total_ht'],
            'tva_amount' => $totals['tva_amount'],
            'total_ttc' => $totals['total_ttc'],
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
        $facture->load('details', 'client', 'vente', 'devis', 'bonLivraison');
        $companySettings = AppSetting::allAsMap();

        return view('factures.print', compact('facture', 'companySettings'));
    }

    private function validateFacturePayload(Request $request): array
    {
        $data = $request->validate([
            'date_facture' => 'required|date',
            'tva_rate' => 'required|numeric|min:0|max:100',
            'remise_type' => 'required|in:dh,%',
            'remise_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($data['remise_type'] === '%' && (float) ($data['remise_value'] ?? 0) > 100) {
            throw ValidationException::withMessages([
                'remise_value' => 'La remise en pourcentage ne peut pas depasser 100%.',
            ]);
        }

        return $data;
    }

    private function computeTotalsWithHtDiscount(float $baseTtc, float $tvaRate, string $remiseType, float $remiseValue): array
    {
        $baseTtc = max($baseTtc, 0);
        $tvaRate = max($tvaRate, 0);
        $factor = 1 + ($tvaRate / 100);
        $baseHt = $factor > 0 ? round($baseTtc / $factor, 2) : $baseTtc;

        $remiseValue = max($remiseValue, 0);
        if ($remiseType === '%') {
            $remiseValue = min($remiseValue, 100);
            $remiseHt = round($baseHt * ($remiseValue / 100), 2);
        } else {
            $remiseHt = round(min($remiseValue, $baseHt), 2);
        }

        $totalHt = round(max($baseHt - $remiseHt, 0), 2);
        $tvaAmount = round($totalHt * ($tvaRate / 100), 2);
        $totalTtc = round($totalHt + $tvaAmount, 2);

        return [
            'base_ht' => $baseHt,
            'remise_ht' => $remiseHt,
            'total_ttc' => $totalTtc,
            'total_ht' => $totalHt,
            'tva_amount' => $tvaAmount,
        ];
    }

    private function legalFieldsPayload(): array
    {
        $settings = AppSetting::allAsMap();

        return [
            'legal_company_name' => $settings['company_name'] ?? env('LEGAL_COMPANY_NAME', env('COMPANY_NAME', config('app.name'))),
            'legal_ice' => $settings['company_ice'] ?? env('LEGAL_ICE'),
            'legal_rc' => $settings['company_rc'] ?? env('LEGAL_RC'),
            'legal_if' => $settings['company_if'] ?? env('LEGAL_IF'),
            'legal_cnss' => $settings['company_cnss'] ?? env('LEGAL_CNSS'),
            'legal_address' => $settings['company_address'] ?? env('COMPANY_ADDRESS'),
            'legal_phone' => $settings['company_phone'] ?? env('COMPANY_PHONE'),
            'legal_email' => $settings['company_email'] ?? env('COMPANY_EMAIL'),
        ];
    }

    private function generateFactureNumber(): string
    {
        $today = now()->format('Ymd');
        $last = Facture::whereDate('created_at', now()->toDateString())
            ->orderByDesc('id')
            ->first();

        $seq = 1;
        if ($last && preg_match('/FAC-\d{8}-(\d+)/', $last->numero, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return 'FAC-' . $today . '-' . str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
    }
}
