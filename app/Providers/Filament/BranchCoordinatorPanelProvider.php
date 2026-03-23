<?php

namespace App\Providers\Filament;

use App\Filament\Pages\AccountsReceivableReport;
use App\Filament\Pages\CreditPaymentResource;
use App\Models\User;
use App\Filament\Pages\IncomeBranchsReport;
use App\Filament\Resources\Branches\BranchResource;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Promotions\PromotionResource;
use App\Filament\Resources\Services\ServiceResource;
use App\Filament\Resources\Suppliers\SupplierResource;
use App\Filament\Resources\Warehouses\WarehouseResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

class BranchCoordinatorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('branch-coordinator')
            ->path('branch-coordinator')
            ->resources([
                WarehouseResource::class,   // Interfaz de Admin

                BranchResource::class, // Interfaz compartida
                CustomerResource::class,
                PromotionResource::class,
                ServiceResource::class,
                ProductResource::class,
                CategoryResource::class,
                SupplierResource::class,
                // ProductResource::class // Interfaz de BranchManager
            ])
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/BranchCoordinator/Pages'), for: 'App\Filament\BranchCoordinator\Pages')
            ->pages([
                Dashboard::class,
                AccountsReceivableReport::class,
                AccountsReceivableReport::class,
                IncomeBranchsReport::class,
            ])
            ->discoverWidgets(in: app_path('Filament/BranchCoordinator/Widgets'), for: 'App\Filament\BranchCoordinator\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
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
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Blade::render("@vite(['resources/css/cerisier.css', 'resources/css/app.css', 'resources/js/app.js'])"),
            )
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->authGuard('web')
            ->authMiddleware([
                //Authenticate::class,
                \App\Http\Middleware\RedirectIfNoPanelAccess::class,
            ]);
    }

    public function canAccessPanel(User $user): bool
    {
        return $user->hasRole('branch-coordinator');
    }
}
