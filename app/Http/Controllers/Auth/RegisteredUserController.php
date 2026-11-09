<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            $user = DB::transaction(function () use ($request) {
                return User::create([
                    'first_name' => trim($request->first_name),
                    'last_name' => trim($request->last_name),
                    'email' => strtolower(trim($request->email)),
                    'password' => Hash::make($request->password),
                    'role' => 'user', // Default role එක සාමාන්‍ය පාරිභෝගිකයෙක් ලෙස
                    'status' => 'active',
                ]);
            });

            event(new Registered($user));

            Auth::login($user);

            return redirect('/')->with('success', 'Your account has been created successfully! Welcome to Wiki Store.');
        } catch (\Throwable $e) {
            Log::error('User registration error: ' . $e->getMessage(), [
                'email' => $request->email,
                'ip' => $request->ip(),
            ]);

            return back()->withInput()->withErrors([
                'email' => __('An error occurred while creating your account. Please try again or contact support.'),
            ]);
        }
    }
}
