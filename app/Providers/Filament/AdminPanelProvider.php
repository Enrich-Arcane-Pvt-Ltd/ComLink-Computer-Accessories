<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            // ->login()
            // ->login(Login::class)
            ->profile(false)
            ->userMenuItems([
                MenuItem::make()
                    ->label('Back to site')
                    ->icon('heroicon-o-home')
                    ->url('/'),
            ])
            ->sidebarCollapsibleOnDesktop()
            ->collapsedSidebarWidth('4.5rem')
            ->globalSearchDebounce(500)
            ->colors([
                'primary' => [
                    50 => '#f2f4f8',
                    100 => '#e3e7f0',
                    200 => '#c1cae0',
                    300 => '#90a1ca',
                    400 => '#5b73ae',
                    500 => '#3c5491',
                    600 => '#2b3e73',
                    700 => '#212f59',
                    800 => '#192341', // Midnight Navy
                    900 => '#141b31',
                    950 => '#0d1120',
                ],
                'danger' => [
                    50 => '#fff1f1',
                    100 => '#ffe1e1',
                    200 => '#ffc9c9',
                    300 => '#ffa3a3',
                    400 => '#ff6e6e',
                    500 => '#f83b3b',
                    600 => '#d82323', // Red Accent
                    700 => '#b51a1a',
                    800 => '#961919',
                    900 => '#7d1a1a',
                    950 => '#440a0a',
                ],
                'gray' => Color::Zinc,
            ])
            ->font('Plus Jakarta Sans')
            ->brandName('Wiki-Store')
            ->brandLogo(fn () => view('filament.components.brand'))
            ->brandLogoHeight('3.5rem')
            ->darkMode(true)
            ->favicon(asset('favicon.ico'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->navigationGroups([
                'User Management',
                'Catalog Management',
                'Order Management',
                'Inventory Management',
                'Stock Management',
                'Supplier Management',
                'Marketing',
                'System Management',
            ])
            ->databaseNotifications()

            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,

                // VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
