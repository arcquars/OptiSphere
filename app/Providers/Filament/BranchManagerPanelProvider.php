<?php

namespace App\Providers\Filament;

use App\Filament\BranchManager\Pages\BranchManager;
use App\Filament\BranchManager\Pages\SalesReport;
use App\Filament\BranchManager\Widgets\StatsOverviewWidget;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Branch;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use function Filament\Support\original_request;

class BranchManagerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('branch-manager')
            ->path('branch-manager')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/BranchManager/Resources'), for: 'App\Filament\BranchManager\Resources')
            ->discoverPages(in: app_path('Filament/BranchManager/Pages'), for: 'App\Filament\BranchManager\Pages')
            ->pages([
                Dashboard::class,
                BranchManager::class,
                SalesReport::class
            ])
            ->discoverWidgets(in: app_path('Filament/BranchManager/Widgets'), for: 'App\Filament\BranchManager\Widgets')
            ->widgets([
//                StatsOverviewWidget::class,
//                AccountWidget::class,
//                FilamentInfoWidget::class,
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
            ])
            ->userMenuItems([
                'logout' => fn (Action $action) => $action->label('Log out')->url(fn (): string => route('logout'))->postToUrl()->color('danger'),
                Action::make('panel-admin')
                    ->label('Volver Admin')
                    ->url(fn (): string => '/admin')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->visible(fn (): bool => auth()->user()->hasRole('admin'))
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Blade::render("@vite(['resources/css/app.css', 'resources/js/app.js'])"),
            )
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->authGuard('web')
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
//                Log::info("xxxxxxdd " . auth()->check());
                return $builder->items([
                    NavigationItem::make('Dashboard')
                        ->icon('heroicon-o-home')
                        ->isActiveWhen(fn (): bool => original_request()->routeIs('filament.admin.pages.dashboard'))
                        ->url(fn (): string => Dashboard::getUrl()),
                    ...$this::getNavigationItems(),
                    NavigationItem::make('SalesReport')
                        ->label('Reporte de ventas')
//                        ->icon('heroicon-o-home')
                        ->icon('fas-table-list')
                        ->isActiveWhen(fn (): bool => original_request()->routeIs('filament.branch-manager.pages.sales-report'))
                        ->url(fn (): string => SalesReport::getUrl()),
                ]);
            });
//            ->navigationItems([
//                NavigationItem::make('dashboard')
//                    ->label(fn (): string => __('filament-panels::pages/dashboard.title'))
//                    ->url(fn (): string => Dashboard::getUrl())
//                    ->isActiveWhen(fn () => original_request()->routeIs('filament.admin.pages.dashboard')),
//            ]);
    }

    public static function getNavigationItems(): array
    {
        $items = [];

        Log::info("xxxxxxdd " . auth()->check());
        $branches = collect();
        if(auth()->user()->hasRole('admin')){
            $branches = Branch::where('is_active', true)->get();
        } elseif (auth()->user()->hasRole('branch-manager')) {
            $branches = User::find(Auth::id())->branches;
        }

        foreach ($branches as $i => $branch){
            if($branch){
                $items[] = NavigationItem::make()
                    ->label("Sucursal {$branch->name}")
                    ->icon('heroicon-o-building-office')
                    ->url(route(BranchManager::getRouteName(), ['branchId' => $branch->id]))
                    ->isActiveWhen(fn () => request()->route('branchId') == $branch->id);
            }

        }


        return $items;
    }

}
