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
        spl_autoload_register(function ($class) {
            $prefixes = [
                'Barryvdh\\DomPDF\\' => base_path('vendor/barryvdh/laravel-dompdf/src/'),
                'Dompdf\\' => base_path('vendor/dompdf/dompdf/src/'),
                'FontLib\\' => base_path('vendor/dompdf/php-font-lib/src/FontLib/'),
                'Svg\\' => base_path('vendor/dompdf/php-svg-lib/src/Svg/'),
                'Sabberworm\\CSS\\' => base_path('vendor/sabberworm/php-css-parser/src/'),
            ];
            foreach ($prefixes as $prefix => $baseDir) {
                $len = strlen($prefix);
                if (strncmp($prefix, $class, $len) === 0) {
                    $relativeClass = substr($class, $len);
                    $file = $baseDir.str_replace('\\', '/', $relativeClass).'.php';
                    if (file_exists($file)) {
                        require_once $file;

                        return;
                    }
                }
            }
        });

        if (class_exists(\Barryvdh\DomPDF\ServiceProvider::class)) {
            $this->app->register(\Barryvdh\DomPDF\ServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->isLocal() && ! app()->runningInConsole()) {
            ini_set('max_execution_time', '120');
            set_time_limit(120);
        }

        Vite::prefetch(concurrency: 3);

        Event::listen(function (Login $event) {
            // දැනට Log වෙන්නේ Admin Panel එකටද කියලා මෙහෙම බලන එක ෂුවර්
            $panel = Filament::getCurrentPanel();

            if ($panel && ($panel->getId() === 'admin')) {
                Notification::make()
                    ->title('Welcome back, '.$event->user->first_name.'!')
                    ->body('Have a productive day at Wiki-Store.')
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
