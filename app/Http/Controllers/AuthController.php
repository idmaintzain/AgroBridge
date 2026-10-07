<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::in(['buy', 'sell', 'both'])],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'buys' => in_array($data['role'], ['buy', 'both'], true),
            'sells' => in_array($data['role'], ['sell', 'both'], true),
            'is_admin' => false,
        ]);

        Auth::login($user);

        return redirect()->route('desk')->with('status', 'Your account is ready. A new registration starts with an empty wallet.');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt(['email' => $data['email'], 'password' => $data['password']], $request->boolean('remember'))) {
            return back()->withInput($request->only('email'))->with('error', 'Those details do not match an account.');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('desk'));
    }

    public function demo(string $role)
    {
        abort_unless(app()->environment('local'), 404);

        $email = match ($role) {
            'farmer' => 'farmer@agrobridge.test',
            'buyer' => 'buyer@agrobridge.test',
            'both' => 'ada@agrobridge.test',
            'admin' => 'admin@agrobridge.test',
            default => abort(404),
        };

        $user = User::query()->where('email', $email)->firstOrFail();
        Auth::login($user);
        request()->session()->regenerate();

        return redirect()->route('desk');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('market');
    }
}
