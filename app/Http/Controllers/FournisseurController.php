<?php

namespace App\Http\Controllers;

use App\Fournisseur;
use Illuminate\Http\Request;

class FournisseurController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role === 'agent') {
            return redirect()->route('fournisseurs.create');
        }

        $query = Fournisseur::query();

        if ($request->filled('nom')) {
            $query->where('nom', 'like', '%' . $request->nom . '%');
        }

        if ($request->filled('type')) {
            if ($request->type === 'Societe') {
                $query->whereNotNull('societe')->where('societe', 'Societe');
            } elseif ($request->type === 'Client comptoir') {
                $query->whereNotNull('societe')->where('societe', 'Client comptoir');
            }
        }

        $fournisseurs = $query->latest()->get();

        return view('fournisseurs.index', compact('fournisseurs'));
    }

    public function create()
    {
        return view('fournisseurs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'nullable|string',
            'telephone' => 'nullable|string',
            'societe' => 'nullable|string',
            'adresse' => 'nullable|string',
        ]);

        $data = $request->only(['nom', 'email', 'telephone', 'societe', 'adresse']);
        Fournisseur::create($data);

        return redirect()->route('fournisseurs.index')->with('success', 'Fournisseur ajoute avec succes.');
    }

    public function show($id)
    {
    }

    public function edit(Fournisseur $fournisseur)
    {
        return view('fournisseurs.edit', compact('fournisseur'));
    }

    public function update(Request $request, Fournisseur $fournisseur)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'nullable|email',
            'telephone' => 'nullable|string',
            'societe' => 'nullable|string',
            'adresse' => 'nullable|string',
        ]);

        $data = $request->only(['nom', 'email', 'telephone', 'societe', 'adresse']);
        $fournisseur->update($data);

        return redirect()->route('fournisseurs.index')->with('success', 'Fournisseur modifie avec succes.');
    }

    public function destroy(Fournisseur $fournisseur)
    {
        $fournisseur->delete();
        return redirect()->route('fournisseurs.index')->with('success', 'Fournisseur supprime.');
    }
}