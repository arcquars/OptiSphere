<?php

declare(strict_types=1);

namespace App\Filament\BranchManager\Resources\ProductAuthentications\Pages;

use App\Filament\BranchManager\Resources\ProductAuthentications\ProductAuthenticationResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class ListProductAuthentications extends ListRecords
{
    protected static string $resource = ProductAuthenticationResource::class;

    // Query string (no segmento de ruta): así Filament puede seguir generando
    // internamente la URL de esta página (breadcrumbs, etc.) sin requerirlo.
    #[Url]
    public ?int $branchId = null;

    // Solo lectura y aprobación: sin acción de creación en la cabecera
    protected function getHeaderActions(): array
    {
        return [];
    }

    // Acota el listado a los clientes frecuentes de la sucursal indicada en la URL
    public function table(Table $table): Table
    {
        return parent::table($table)
            ->modifyQueryUsing(fn (Builder $query) => $query->whereHas(
                'frequentCustomer',
                fn (Builder $query) => $query->where('branch_id', $this->branchId)
            ));
    }
}
