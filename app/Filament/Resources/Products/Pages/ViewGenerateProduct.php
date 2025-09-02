<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\EditAction;
use \Filament\Resources\Pages\Page;

class ViewGenerateProduct extends Page
{
    protected static string $resource = ProductResource::class;

    protected static ?string $title = 'Generar productos';

    protected string $view = 'filament.resources.products.pages.view-generate';

}
