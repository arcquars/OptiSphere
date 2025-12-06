<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\OpticalProperty;
use App\Models\Product;
use Filament\Forms\Components\Select;
// Usamos los componentes de Schema para V4 según tu referencia
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Resources\Pages\Page;

class GenerateProductEdit extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string $resource = ProductResource::class;

    protected static ?string $title = 'Editar generar productos';

    protected string $view = 'filament.resources.products.pages.generate-products-edit';

    // Propiedad para almacenar los datos del formulario de esta página (el selector)
    public ?array $data = [];

    public function mount(): void
    {
        // Inicializamos el formulario
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Selección')
                    ->schema([
                        Select::make('selected_base_code')
                            ->label('Código Base')
                            ->options(
                                OpticalProperty::groupBy('base_code')->pluck('base_code', 'base_code')
                            )
                            ->searchable()
                            ->preload()
                            ->live() // Reactivo: recarga la vista al seleccionar
                            ->required(),
                    ])
            ])
            ->statePath('data');
    }
}