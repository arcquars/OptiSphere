<?php

namespace App\Filament\BranchManager\Pages;

use Filament\Pages\Page;
use Filament\Panel;
use Illuminate\Contracts\Support\Htmlable;

class BranchManager extends Page
{
    protected string $view = 'filament.branch-manager.pages.branch-manager';

    protected static ?string $title = 'Sucursal..';

    protected static bool $shouldRegisterNavigation = false;

    public $branch;

    public function getTitle(): string | Htmlable
    {
        // Comprueba si la sucursal se ha cargado correctamente
        if ($this->branch) {
            return "Terminal de Venta - " . $this->branch->name;
        }

        return "Terminal de Venta";
    }

    /**
     * 3. Sobrescribe el método getHeading() para el encabezado H1 dinámico en la página.
     */
    public function getHeading(): string | Htmlable
    {
        if ($this->branch) {
            return "Punto de Venta: " . $this->branch->name;
        }

        return "Punto de Venta";
    }

    /**
     * 4. (Opcional) Sobrescribe el subtítulo para dar más contexto.
     */
    public function getSubheading(): string | Htmlable | null
    {
        if ($this->branch) {
            return "Gestionando ventas para la sucursal seleccionada.";
        }
        return "Por favor, seleccione una sucursal para comenzar.";
    }

    public static function getRoutePath(Panel $panel): string
    {
        return 'store/{branchId}/manage'; // URL: /admin/branches/manage
    }

    public function mount(int $branchId): void
    {
        // Aquí puedes cargar la sucursal o hacer validaciones
        $this->branch = \App\Models\Branch::findOrFail($branchId);
    }

    protected function getViewData(): array
    {
        return [
            'branch' => $this->branch,
        ];
    }
}
