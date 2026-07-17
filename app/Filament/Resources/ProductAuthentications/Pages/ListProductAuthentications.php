<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductAuthentications\Pages;

use App\Filament\Resources\ProductAuthentications\ProductAuthenticationResource;
use Filament\Resources\Pages\ListRecords;

class ListProductAuthentications extends ListRecords
{
    protected static string $resource = ProductAuthenticationResource::class;

    // Solo lectura y aprobación: sin acción de creación en la cabecera
    protected function getHeaderActions(): array
    {
        return [];
    }
}
