<?php

namespace App\Filament\BranchManager\Resources\Customers\Pages;

use App\Filament\BranchManager\Resources\Customers\CustomerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    public $branch_id; // Declaramos la propiedad

    // El método mount recibe automáticamente los parámetros de la URL definida en el Resource
    public function mount(): void
    {
        parent::mount();
        $this->branch_id = request()->route('branch_id');
    }

    // Filtramos la tabla usando el ID capturado
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->when($this->branch_id, fn ($query) => $query->where('branch_id', $this->branch_id));
    }
}
