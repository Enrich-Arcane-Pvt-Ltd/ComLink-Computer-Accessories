<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    protected static string $view = 'filament.pages.auth.login';

    public function authenticate(): ?LoginResponse
    {
        try {
            $data = $this->form->getState();
        } catch (\Throwable $th) {
            return null;
        }

        if (! Auth::attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            throw ValidationException::withMessages([
                'clear' => __('filament-panels::pages/auth/login.messages.failed'),
            ]);
        }

        $user = Auth::user();

        if ($user->status !== 'active' || ! in_array($user->role, ['super-admin', 'admin'])) {

            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();

            throw ValidationException::withMessages([
                'clear' => 'Access denied. Customer accounts cannot be used to log into the Admin Panel.',
            ]);
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }
}

// namespace App\Filament\Pages\Auth;

// use Filament\Pages\Auth\Login as BaseLogin;
// use Illuminate\Contracts\View\View;

// class Login extends BaseLogin
// {
//     protected static string $view = 'filament.pages.auth.login';

//     public function render(): View
//     {
//         return view(static::$view)
//             ->layout('filament-panels::components.layout.base', [
//                 'livewire' => $this,
//             ]);
//     }
// }
