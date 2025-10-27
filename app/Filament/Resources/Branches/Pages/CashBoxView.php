<?php

namespace App\Filament\Resources\Branches\Pages;

use App\Filament\Resources\Branches\BranchResource;
use App\Models\Branch;
use App\Models\CashBoxClosing;
use Filament\Resources\Pages\Page;

class CashBoxView extends Page
{
    protected static string $resource = BranchResource::class;

    protected static ?string $title = 'Detalle de Caja';

    protected string $view = 'filament.resources.branches.pages.cash-box-view';

    public CashBoxClosing $cashBc;

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [];
        $currentPageTitle = $this->getTitle(); // 'Detalle de Caja'

        // 1. Sucursales (Base: Link al listado principal del Resource)
        // Se asume que BranchResource::getUrl('index') genera la ruta base de Sucursales.
        $breadcrumbs[BranchResource::getUrl('index')] = BranchResource::getTitleCasePluralModelLabel();

        // 2. Reporte de Caja (Página Intermedia)
        // Debes reemplazar 'reporte-caja-index' con el nombre de la ruta que has definido
        // para tu clase App\Filament\Pages\ReporteCajaIndex.
//        $reporteCajaUrl = url('/' . Filament::getPanel()->getPath() . '/' . ReporteCajaIndex::getSlug());

        // Si ReporteCajaIndex extiende Filament\Pages\Page, utiliza su método getUrl()
         $reporteCajaUrl = CashBoxReport::getUrl(['branch_id' => $this->cashBc->branch_id]);

        $breadcrumbs[$reporteCajaUrl] = 'Reporte de Caja';

        // 3. Detalle de Caja (Página Actual)
        // Se usa '#' para indicar que la página actual no es un enlace.
        $breadcrumbs[''] = $currentPageTitle;

        return $breadcrumbs;
    }

    public function mount(int $cashBoxClosingId): void
    {
        $this->cashBc = CashBoxClosing::find($cashBoxClosingId);
    }

    protected function getViewData(): array
    {
        return [
            'cashBoxClosing' => $this->cashBc,
        ];
    }
}
