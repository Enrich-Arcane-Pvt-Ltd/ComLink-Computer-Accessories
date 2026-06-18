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
            ->login()
            ->profile(false)
            ->userMenuItems([
                MenuItem::make()
                    ->label('Back to site')
                    ->icon('heroicon-o-home')
                    ->url('/'),
            ])
            // --- Branding ---
            ->brandName('Vicky-Store')
            ->sidebarCollapsibleOnDesktop()
            ->brandLogo(fn() => view('filament.components.brand')) // අර හදපු file එක link කරනවා
            ->brandLogoHeight('3.5rem') // උස auto දෙනවා design එක කැඩෙන්නේ නැති වෙන්න
            // Logo images public folder එකේ තිබිය යුතුය
            // ->brandLogo(asset('images/logo.png'))
            // ->darkModeBrandLogo(asset('images/logo.png'))
            // ->brandLogoHeight('3rem')
            ->favicon(asset('favicon.ico'))
            ->login(Login::class)
            // ->viteTheme('resources/css/app.css')
            // ->viteTheme('resources/css/app.css')
            ->globalSearchDebounce(500)
            ->navigationGroups([
                'User Management',
                'Catalog Management',
                'Marketing',
                'System Management',
            ])

            // --- Fonts ---
            ->font('Plus Jakarta Sans')

            // --- Colors ---
            // මෙතන අපි Tailwind එකේ දැම්ම පාටම Filament එකටත් දෙනවා
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

            // ->renderHook(
            //     'panels::topbar.start',
            //     fn() => view('filament.hooks.header-logo')
            // )

            // --- Sidebar Customization ---
            ->sidebarCollapsibleOnDesktop()
            ->collapsedSidebarWidth('4.5rem')

            // --- Database Notifications ---
            ->databaseNotifications()

            // --- Resources & Pages ---
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
