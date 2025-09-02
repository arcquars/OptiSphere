<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create-base-code')
                ->label('Crear con códigos Base')
                ->url(fn (): string => route('filament.admin.resources.products.generate'))
                ->color('success'),
            CreateAction::make()
        ];
    }
}
