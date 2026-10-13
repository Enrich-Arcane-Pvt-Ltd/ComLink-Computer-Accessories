<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): \Symfony\Component\HttpFoundation\Response
    {
        $request->authenticate();
        $request->session()->regenerate();

        return $this->redirectByRole();
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function redirectByRole(): \Symfony\Component\HttpFoundation\Response
    {
        $role = Auth::user()->role ?? null;

        $destination = match (true) {
            in_array($role, ['super-admin', 'admin'])    => '/admin', // requires AdminPanelProvider
            in_array($role, ['user'])                 => '/',
            default                                      => '/login',
        };

        // Inertia::location() sends a 409 + X-Inertia-Location header.
        // The Inertia JS client then does window.location = url (full page load).
        // This completely exits the Inertia SPA — Filament loads fresh.
        return Inertia::location($destination);
    }
}
