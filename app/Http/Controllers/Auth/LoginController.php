<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function redirectTo()
    {
        return '/caisse';
    }

    public function login(Request $request)
    {
        $request->validate([
            'access_code' => 'required|string|regex:/^[0-9]{4,12}$/',
        ], [
            'access_code.required' => "Le code d'acces est obligatoire.",
            'access_code.regex' => "Le code d'acces doit contenir entre 4 et 12 chiffres.",
        ]);

        $accessCode = trim((string) $request->input('access_code'));
        $user = User::where('access_code', $accessCode)->first();

        if (!$user) {
            return back()
                ->withErrors(['access_code' => "Code d'acces invalide."])
                ->withInput();
        }

        Auth::login($user, false);
        $request->session()->regenerate();

        Session::put('logged', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        return redirect()->intended('/caisse');
    }

    protected function authenticated(Request $request, $user)
    {
        Session::put('logged', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        return redirect()->intended('/caisse');
    }
}