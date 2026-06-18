<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Event::listen(function (Login $event) {
            // දැනට Log වෙන්නේ Admin Panel එකටද කියලා මෙහෙම බලන එක ෂුවර්
            $panel = Filament::getCurrentPanel();

            if ($panel && ($panel->getId() === 'admin')) {
                Notification::make()
                    ->title('Welcome back, ' . $event->user->first_name . '!')
                    ->body('Have a productive day at Vicky-Store.')
                    ->success()
                    ->send();
            }
        });

        Event::listen(Login::class, function ($event) {
            activity()
                ->causedBy($event->user)
                ->withProperties(['ip' => Request::ip(), 'agent' => Request::userAgent()])
                ->log('logged_in');
        });

        // 2. Log Logout Activity
        Event::listen(Logout::class, function ($event) {
            if ($event->user) {
                activity()
                    ->causedBy($event->user)
                    ->withProperties(['ip' => Request::ip(), 'agent' => Request::userAgent()])
                    ->log('logged_out');
            }
        });
    }
}
