<?php

namespace App\Filament\BranchManager\Pages;

use App\Models\CashBoxClosing;
use Filament\Pages\Page;
use Filament\Panel;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Contracts\View\View;

class BranchManager extends Page
{
    protected string $view = 'filament.branch-manager.pages.branch-manager';

    protected static ?string $title = 'Sucursal';

    protected static bool $shouldRegisterNavigation = false;

    public $branch;

    public function getHeader(): ?View
    {
        $isOpenCashBox = CashBoxClosing::isOpenCashBoxByBranchAndUser($this->branch->id, Auth::id());
        $isFacturable = $this->branch->is_facturable;
        return view('filament.branch-manager.pages.header', 
        [
            'branch' => $this->branch, 
            'isOpenCashBox' => $isOpenCashBox, 
            'isFacturable' => $isFacturable
        ]);
    }

    // public function getTitle(): string | Htmlable
    // {
    //     // Comprueba si la sucursal se ha cargado correctamente
    //     if ($this->branch) {
    //         return "Terminal de Venta - " . $this->branch->name;
    //     }

    //     return "Terminal de Venta";
    // }

    /**
     * 3. Sobrescribe el método getHeading() para el encabezado H1 dinámico en la página.
     */
    // public function getHeading(): string | Htmlable
    // {
    //     if ($this->branch) {
    //         return "Punto de Venta: " . $this->branch->name;
    //     }

    //     return "Punto de Venta";
    // }

    /**
     * 4. (Opcional) Sobrescribe el subtítulo para dar más contexto.
     */
    public function getSubheading(): string | Htmlable | null
    {
        if ($this->branch) {
            if(CashBoxClosing::isOpenCashBoxByBranchAndUser($this->branch->id, Auth::id())){
                return new HtmlString(Blade::render('
                    <div class="flex justify-between items-center w-full">
                        <p class="text-success">
                            Gestionando ventas para la sucursal seleccionada <i class="fa-solid fa-lock-open"></i>
                        </p>
                        <div>
                            <livewire:branch.siat-connection-status :branch="$branch" />
                        </div>
                    </div>
                ', ['branch' => $this->branch]));
            } else {
                return new HtmlString('<p class="text-error">Caja se encuentra cerrada <i class="fa-solid fa-lock"></i></p>');
            }

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
