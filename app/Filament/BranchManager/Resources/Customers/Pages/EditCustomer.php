<?php

namespace App\Filament\BranchManager\Resources\Customers\Pages;

use App\Filament\BranchManager\Resources\Customers\CustomerResource;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    public ?string $branch_id = null;

    // 2. Capturamos el valor al iniciar la página
    public function mount($record): void
    {
        parent::mount($record);

        // Intentamos obtenerlo de la ruta o del query string
        $this->branch_id = request()->route('branch_id') ?? request()->query('branch_id');
    }

    protected function getRedirectUrl(): string
    {
        $panelId = Filament::getCurrentPanel()->getId();
        //$branchId = request()->route('branch_id') ?? request()->query('branch_id');
        // dd($this->branch_id);
        // Al guardar, regresa a la lista pasando el branch_id que está en la URL actual
        return $this->getResource()::getUrl('index', [
            'branch_id' => $this->branch_id
        ]);
    }
    // protected function getRedirectUrl(): string
    // {
    //     // 1. Detectamos el panel actual (admin o branchManager)
    //     $panelId = Filament::getCurrentPanel()->getId();

    //     // 2. Capturamos el branch_id que venía en la URL de edición
    //     $branchId = request()->route('branch_id') ?? request()->query('branch_id');

    //     // 3. Generamos la URL de retorno
    //     // Filament::getUrl() generará automáticamente /admin/customers o /branch-manager/customers
    //     return $this->getResource()::getUrl('index', [
    //         'branch_id' => $branchId, // Mantenemos el filtro
    //     ]);
    // }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
