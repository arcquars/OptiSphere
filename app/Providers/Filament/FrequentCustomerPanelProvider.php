<?php

namespace App\Providers\Filament;

use App\Http\Middleware\RedirectIfNoPanelAccess;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class FrequentCustomerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('frequent-customer')
            ->path('frequent-customer')
            ->brandName('Cliente Frecuente')
            ->favicon(asset('favicon.ico'))
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->globalSearch(false)
            ->discoverResources(in: app_path('Filament/FrequentCustomer/Resources'), for: 'App\Filament\FrequentCustomer\Resources')
            ->discoverPages(in: app_path('Filament/FrequentCustomer/Pages'), for: 'App\Filament\FrequentCustomer\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/FrequentCustomer/Widgets'), for: 'App\Filament\FrequentCustomer\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                'web',
                'auth',
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
                // Fuerza logout si el usuario no tiene el rol o está inactivo (is_active = false)
                RedirectIfNoPanelAccess::class,
            ])
            ->authGuard('web');
    }
}
