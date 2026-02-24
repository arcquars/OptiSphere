<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Models\Branch;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get as UtilitiesGet;
use Illuminate\Validation\Rules\Unique; // Importante para modificar la regla

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombres')
                    ->required(),
                TextInput::make('razon_social')
                    ->label('Razon Social')
                    ->required(),
                TextInput::make('nit')
                    ->label('NIT')
                    ->required()
                    /**
                     * Validación de unicidad por branch_id
                     */
                    ->unique(
                        table: 'customers', // Reemplaza por el nombre real de tu tabla si es distinto
                        column: 'nit',
                        ignorable: fn ($record) => $record, // Permite guardar cambios al editar el mismo registro
                        modifyRuleUsing: function (Unique $rule, UtilitiesGet $get) {
                            // Obtenemos el ID de la sucursal seleccionada en el formulario
                            $branchId = $get('branch_id');

                            // Si hay una sucursal seleccionada, añadimos la condición a la regla unique
                            return $rule->where('branch_id', $branchId);
                        }
                    )
                    // Mensaje personalizado para el usuario
                    ->validationMessages([
                        'unique' => 'Este NIT ya está registrado en la sucursal seleccionada.',
                    ]),
                TextInput::make('address')->label('Dirección'),
                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->email(),
                Select::make('document_type')
                    ->label('Tipo de documento')->options(
                        config('amyr.tipo_documento_identidad')
                    )->required(),
                TextInput::make('complement')->label('Complemento'),
                TextInput::make('phone')
                    ->label('Teléfono')
                    ->tel(),
                TextInput::make('contact_info')
                    ->label('Información de contacto'),
                Select::make('type')
                    ->label('Tipo')
                    ->options(config('cerisier.tipo_cliente'))
                    ->default('normal')
                    ->required(),
                Select::make('branch_id')
                    ->label('Sucursal')
                    ->options(Branch::query()->where('is_active', 1)->pluck('name', 'id'))
                    ->searchable()
                    ->required() // Es recomendable que sea requerido si la unicidad depende de él
                    ->live(),
                Toggle::make('can_buy_on_credit')
                    ->label('Credito'),
                TextInput::make('credit_limit')
                    ->label('Limite de credito')
                    ->numeric(),

            ]);
    }
}
