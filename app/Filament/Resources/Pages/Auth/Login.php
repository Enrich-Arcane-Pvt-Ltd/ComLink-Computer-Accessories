<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\View\View;

class Login extends BaseLogin
{
    protected static string $view = 'filament.pages.auth.login';

    public function render(): View
    {
        return view(static::$view)
            ->layout('filament-panels::components.layout.base', [
                'livewire' => $this,
            ]);
    }
}
