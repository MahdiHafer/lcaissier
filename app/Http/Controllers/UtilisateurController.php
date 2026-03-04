<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UtilisateurController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role !== 'admin') {
                abort(403);
            }
            return $next($request);
        });
    }

    public function index()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'access_code' => 'required|string|regex:/^[0-9]{4,12}$/|unique:users,access_code',
            'role' => 'required|in:admin,agent',
            'password' => 'required|string|min:4',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'access_code' => $validated['access_code'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('users.index')->with('success', 'Utilisateur ajoute avec succes.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'access_code' => 'required|string|regex:/^[0-9]{4,12}$/|unique:users,access_code,' . $user->id,
            'role' => 'required|in:admin,agent',
            'password' => 'nullable|string|min:4',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'access_code' => $validated['access_code'],
            'role' => $validated['role'],
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        return redirect()->route('users.index')->with('success', 'Utilisateur mis a jour.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Utilisateur supprime.');
    }
}