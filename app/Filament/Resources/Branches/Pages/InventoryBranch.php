<?php

namespace App\Filament\Resources\Branches\Pages;

use App\Filament\Resources\Branches\BranchResource;
use App\Models\Branch;
use Filament\Resources\Pages\Page;

class InventoryBranch extends Page
{
    protected static string $resource = BranchResource::class;

    protected static ?string $title = 'Inventario de Sucursal';

    protected string $view = 'filament.resources.branches.pages.inventory-branch';

    public Branch $branch;

    public function mount(int $branch_id): void
    {
        $this->branch = Branch::find($branch_id);
    }

    protected function getViewData(): array
    {
        return [
            'branch' => $this->branch,
        ];
    }
}
