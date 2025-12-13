<?php

namespace App\Filament\Resources\Branches\Pages;

use App\Filament\Resources\Branches\BranchResource;
use App\Models\Branch;
use Filament\Resources\Pages\Page;

class SiatManager extends Page
{
    protected static string $resource = BranchResource::class;

    // protected static ?string $title = 'Administrar SIAT';

    protected string $view = 'filament.resources.branches.pages.siat-manager';

    public Branch $branch;

    public int $sucursalId = 0;
    public int $puntoVentaId = 0;

    public bool $activeSiat = false;

    public function mount(int $branch_id): void
    {
        $this->branch = Branch::find($branch_id);

        if($this->branch->amyrConnectionBranch){
            $this->sucursalId = $this->branch->amyrConnectionBranch->sucursal;
            $this->puntoVentaId = $this->branch->amyrConnectionBranch->point_sale;
            $this->activeSiat = $this->branch->is_facturable;
        }
    }

    protected function getViewData(): array
    {
        // $urlInvoiceConfig = InvoiceConfig::getUrl(['branch_id' => $this->branch->id]);
        // return [
        //     'urlInvoiceConfig' => $urlInvoiceConfig,
        //     'branch' => $this->branch,
        // ];
        $urlSiatConfig = SiatConfig::getUrl(['branch_id' => $this->branch->id]);
        return [
            'urlSiatConfig' => $urlSiatConfig,
            'branch' => $this->branch,
        ];
    }
    
    public function getTitle(): string
    {
        return "{$this->branch->name} - Administrar SIAT";
    }
    
}
