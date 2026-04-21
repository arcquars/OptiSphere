<?php

namespace App\Filament\BranchManager\Resources\Customers\Pages;

use App\Filament\BranchManager\Resources\Customers\CustomerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

     public string|int $branchId = 0;

    public function mount(): void
    {
        $this->branchId = request()->route('branch_id') ?? 0;

        parent::mount();

        // Pre-llenar el campo hidden branch_id en el formulario
        $this->form->fill([
            'branch_id' => $this->branchId,
        ]);
    }

    // Inyectar branch_id en los datos antes de guardar
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['branch_id'] = $this->branchId;

        return $data;
    }

    public function getBreadcrumbs(): array
    {
        return [
            CustomerResource::getUrl('index', ['branch_id' => $this->branchId]) => 'Clientes',
            'Crear',
        ];
    }

    // Redirigir al index de la misma sucursal después de crear
    protected function getRedirectUrl(): string
    {
        return CustomerResource::getUrl('index', [
            'branch_id' => $this->branchId,
        ]);
    }
}
