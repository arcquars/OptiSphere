<?php

namespace App\Filament\Resources\Branches\Pages;

use App\Filament\Resources\Branches\BranchResource;
use App\Models\Branch;
use App\Models\CashBoxClosing;
use App\Services\CashClosingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Resources\Pages\Page;
use Livewire\Attributes\Computed;

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
        $breadcrumbs[BranchResource::getUrl('index')] = BranchResource::getTitleCasePluralModelLabel();
        $reporteCajaUrl = CashBoxReport::getUrl(['branch_id' => $this->cashBc->branch_id]);
        $breadcrumbs[$reporteCajaUrl] = 'Reporte de Caja';
        $breadcrumbs[''] = $currentPageTitle;

        return $breadcrumbs;
    }

    public function mount(CashClosingService $svc, int $cashBoxClosingId): void
    {
        $this->cashBc = CashBoxClosing::find($cashBoxClosingId);
    }

    protected function getViewData(): array
    {
        return [
            'cashBoxClosing' => $this->cashBc,
        ];
    }

    #[Computed]
    public function totals(): array
    {
        $svc = app(CashClosingService::class);

        $closingTime = (strcmp($this->cashBc->status, CashBoxClosing::STATUS_OPEN) == 0)?
            null : $this->cashBc->closing_time;
//        dd($this->cashBc->opening_time . " || " . $closingTime);
        return $svc->computeTotals(
            closing: $this->cashBc,
            from: $this->cashBc->opening_time,
            until: $closingTime,
            userIdFilter: $this->cashBc->user_id,
        );
    }
}
