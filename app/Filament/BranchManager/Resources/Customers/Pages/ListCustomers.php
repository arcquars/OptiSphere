<?php

namespace App\Filament\BranchManager\Resources\Customers\Pages;

use App\Filament\BranchManager\Resources\Customers\CustomerResource;
use App\Models\Branch;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\BranchManager\Resources\Customers\Tables\CustomersTable;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    public string|int $branch_id = 0;


    // El método mount recibe automáticamente los parámetros de la URL definida en el Resource
    public function mount(): void
    {        
        $this->branch_id = request()->route('branch_id') ?? 0;
        $branchExists = Branch::where('id', $this->branch_id)->exists();
        if (!$branchExists) {
            abort(404);
        }
        parent::mount();
    }

    public function table(Table $table): Table
    {
        return CustomersTable::configure($table, $this->branch_id);
    }

    public function getBreadcrumbs(): array
    {
        return [
            CustomerResource::getUrl('index', ['branch_id' => $this->branch_id]) => 'Clientes',
            'Crear',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->url(CustomerResource::getUrl('create', [
                    'branch_id' => $this->branch_id,
                ])),
        ];
    }

}
