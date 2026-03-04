<?php

namespace App\Http\Controllers;

use App\Color;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    public function index()
    {
        $colors = Color::orderBy('name')->get();
        return view('colors.index', compact('colors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:colors,name',
            'hex_code' => 'nullable|string|max:7',
        ]);

        Color::create([
            'name' => $request->name,
            'hex_code' => $request->hex_code,
        ]);

        return back()->with('success', 'Couleur ajoutee avec succes.');
    }

    public function update(Request $request, Color $color)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:colors,name,' . $color->id,
            'hex_code' => 'nullable|string|max:7',
        ]);

        $color->update([
            'name' => $request->name,
            'hex_code' => $request->hex_code,
        ]);

        return back()->with('success', 'Couleur modifiee avec succes.');
    }

    public function destroy(Color $color)
    {
        $color->delete();
        return back()->with('success', 'Couleur supprimee avec succes.');
    }
}

