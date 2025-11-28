<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Models\Branch;
use App\Models\SiatDataActividad;
use App\Models\SiatDataProducto;
use App\Models\SiatDataUnidadMedida;
use App\Models\SiatSucursalPuntoVenta;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ServiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')->label('Nombre del Servicio'),
                TextEntry::make('code')->label('Código / SKU'),
                ImageEntry::make('path_image')->label('Imagen'),

                RepeatableEntry::make('categories')->label('Categorías')
                    ->schema([
                        TextEntry::make('name')->label('Nombre'),
                    ])
                    ->columns(2),

                IconEntry::make('is_active')->label('Activo')
                    ->boolean(),
                TextEntry::make('siat_sucursal_punto_venta_id')
                    ->label('Sucursal Punto de Venta SIAT')
                    ->state(function (Model $record) {
                        $siatSpv = SiatSucursalPuntoVenta::find($record->siat_sucursal_punto_venta_id);
                        return $siatSpv? $siatSpv->siatProperty->branch->name . " - " . $siatSpv->sucursal . " - " . $siatSpv->punto_venta : 'N/A';
                }),
                TextEntry::make('siat_data_actividad_code')
                    ->label('Código de Actividad SIAT')
                    ->state(function (Model $record) {
                        $actividad = SiatDataActividad::where('codigo', $record->siat_data_actividad_code)
                            ->where('siat_spv_id', $record->siat_sucursal_punto_venta_id)->first();
                        return $actividad? $actividad->descripcion : 'N/A';
                }),
                TextEntry::make('siat_data_product_code')
                    ->label('Código de Producto SIAT')
                    ->state(function (Model $record) {
                        $actividad = SiatDataProducto::where('codigo_producto', $record->siat_data_product_code)
                            ->where('siat_spv_id', $record->siat_sucursal_punto_venta_id)->first();
                        return $actividad? $actividad->descripcion_producto : 'N/A';
                }),
                TextEntry::make('siat_data_medida_code')
                    ->label('Código de Medida SIAT')
                    ->state(function (Model $record) {
                        $actividad = SiatDataUnidadMedida::where('codigo_clasificador', $record->siat_data_medida_code)
                            ->where('siat_spv_id', $record->siat_sucursal_punto_venta_id)->first();
                        return $actividad? $actividad->descripcion : 'N/A';
                }),
//                TextEntry::make('created_at')
//                    ->dateTime(),
//                TextEntry::make('updated_at')
//                    ->dateTime(),
            ]);
    }
}
