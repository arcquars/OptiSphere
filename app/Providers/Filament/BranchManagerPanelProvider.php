<?php

namespace App\Providers\Filament;

use App\Filament\BranchManager\Pages\BranchManager;
use App\Filament\BranchManager\Pages\CashClosing;
use App\Filament\BranchManager\Pages\SalesReport;
use App\Filament\BranchManager\Resources\CashMovements\CashMovementResource;
use App\Models\Branch;
use App\Models\CashBoxClosing;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationItem;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\View\View;
use function Filament\Support\original_request;
use Illuminate\Support\Facades\DB;

class BranchManagerPanelProvider extends PanelProvider implements HasActions
{
    use InteractsWithActions;

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('branch-manager')
            ->path('branch-manager')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->globalSearch(false)
            ->discoverResources(in: app_path('Filament/BranchManager/Resources'), for: 'App\Filament\BranchManager\Resources')
            ->discoverPages(in: app_path('Filament/BranchManager/Pages'), for: 'App\Filament\BranchManager\Pages')
            ->pages([
                Dashboard::class,
                BranchManager::class,
                SalesReport::class,
                CashClosing::class
            ])
            ->renderHook(
            // Hook: Justo después de la barra de búsqueda global
                PanelsRenderHook::GLOBAL_SEARCH_AFTER,

                // Función que devuelve la vista a renderizar
                fn (): View => view('filament.branch-manager.components.cash-box-button'),
            )
//            ->discoverWidgets(in: app_path('Filament/BranchManager/Widgets'), for: 'App\Filament\BranchManager\Widgets')
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
            ->resources([
                // ... Otros recursos que puedas tener
                CashMovementResource::class, // << RECURSO REGISTRADO AQUÍ
            ])
            ->userMenuItems([
                // 1. Definición del Action que abre la Caja
//                Action::make('open-cash')
//                    ->label(fn () => $this->isCashBoxOpen() ? 'Caja Abierta' : 'Abrir Caja')
//                    ->icon(fn () => $this->isCashBoxOpen() ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed')
//                    ->url(fn (): string => '#')
//                    // Oculta la opción si el usuario no tiene permiso (ej. solo cajeros/gerentes)
//                    ->visible(fn () => Auth::user()->hasRole(['cashier', 'branch-manager', 'admin']))
//                    // El botón solo es visible si NO hay una caja abierta
//                    ->hidden(fn () => $this->isCashBoxOpen())
//                    // --- Configuración del Modal ---
//                    ->modalHeading('Apertura de Caja')
//                    ->modalSubmitActionLabel('Abrir Caja')
//                    ->schema([
//                        // Campo para ingresar el fondo de caja inicial
//                        TextInput::make('initial_cash')
//                            ->label('Fondo de Caja Inicial')
//                            ->numeric()
//                            ->inputMode('decimal')
//                            ->default(100.00) // Valor por defecto común
//                            ->required()
//                            ->helperText('Ingrese la cantidad de efectivo con la que inicia la jornada.')
//                            ->rules(['min:0'])
//                            ->prefix('Bs.'),
//                    ])
//                    // --- Lógica del Botón de Cierre ---
//                    ->action(function (array $data): void {
//                        $user = Auth::user();
//
//                        // 1. Verificar si ya hay una caja abierta (una doble verificación)
//                        $openBox = CashBoxClosing::where('user_id', $user->id)
//                            ->where('branch_id', $user->branch_id)
//                            ->where('status', CashBoxClosing::STATUS_OPEN)
//                            ->first();
//
//                        if ($openBox) {
//                            Notification::make()
//                                ->title('Error de Apertura')
//                                ->body('Ya tienes una caja abierta. Debes cerrarla antes de abrir una nueva.')
//                                ->danger()
//                                ->send();
//                            return;
//                        }
//
//                        // 2. Crear el registro de Apertura
//                        DB::transaction(function () use ($user, $data) {
//                            CashBoxClosing::create([
//                                'branch_id' => $user->branch_id,
//                                'user_id' => $user->id,
//                                'opening_time' => now(),
//                                'initial_cash' => $data['initial_cash'],
//                                'expected_cash_total' => $data['initial_cash'], // Inicialmente es solo el fondo
//                                'actual_cash_total' => 0, // 0 al abrir
//                                'difference' => 0,
//                                'status' => CashBoxClosing::STATUS_OPEN,
//                            ]);
//                        });
//
//                        // 3. Notificación de éxito
//                        Notification::make()
//                            ->title('Caja Abierta')
//                            ->body('Caja abierta exitosamente con fondo de Bs. ' . number_format($data['initial_cash'], 2) . '.')
//                            ->success()
//                            ->send();
//                    }),

                // Opción para Cerrar Caja (Solo visible si hay una caja abierta)
                Action::make('close-cash')
                    ->label('Cerrar Caja')
                    ->icon('heroicon-o-lock-closed')
                    ->visible(fn () => $this->isCashBoxOpen())
                    // Aquí NO usas un modal, sino que lo diriges a la página de Cierre de Caja
                    ->url(fn (): string => CashClosing::getUrl())
                ,
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
                        ->icon('fas-table-list')
                        ->isActiveWhen(fn (): bool => original_request()->routeIs('filament.branch-manager.pages.sales-report'))
                        ->url(fn (): string => SalesReport::getUrl()),
                    NavigationItem::make('CashClosing')
                        ->label('Cierre de caja')
                        ->icon('fas-cash-register')
                        ->isActiveWhen(fn (): bool => original_request()->routeIs('filament.branch-manager.pages.cash-closing'))
                        ->url(fn (): string => CashClosing::getUrl()),
                    NavigationItem::make('CashMovement')
                        ->label('Registro de caja')
                        ->icon('fas-sack-dollar')
                        ->isActiveWhen(fn (): bool => original_request()->routeIs('filament.branch-manager.resources.cash-movements.*'))
                        ->url(fn (): string => CashMovementResource::getUrl()),
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

    protected function isCashBoxOpen(): bool
    {
        $user = Auth::user();
//        if (!$user || !isset($user->branch_id)) {
//            return false; // No autenticado o sin sucursal
//        }

        return CashBoxClosing::where('user_id', $user->id)
            ->where('status', CashBoxClosing::STATUS_OPEN)
            ->exists();
    }

    public function getHeaderActions(): array
    {
        return [
            // Puedes dejarlo vacío
        ];
    }
}
