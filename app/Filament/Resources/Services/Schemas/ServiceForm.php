<?php

namespace App\Filament\Resources\Services\Schemas;

use App\DAOs\SiatApiDocumentoTipoDAO;
use App\Models\AmyrConnectionBranch;
use App\Models\Price;
use App\Models\SiatDataActividad;
use App\Models\SiatDataProducto;
use App\Models\SiatDataUnidadMedida;
use DB;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información Principal')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->unique(column: 'name')
                            ->maxLength(255)
                            ->label('Nombre del Servicio'),

                        TextInput::make('code')
                            ->required()
                            ->maxLength(255)
                            ->label('Código / SKU')
                            ->unique(
                                table: 'services',
                                column: 'name',
                                ignoreRecord: true
                            ),

                        Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->label('Descripción'),

                        // 1. CAMPO PARA LA IMAGEN DEL SERVICIO
                        FileUpload::make('path_image')
                            ->label('Imagen')
                            ->image()
                            ->directory('services-images') // Directorio donde se guardarán las imágenes
                            ->visibility('public') // O 'private' si lo prefieres
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->required()
                            ->default(true)
                            ->label('Activo'),
                    ])
                    ->columns(2),

                Section::make('Clasificación')
                    ->schema([
                        // 2. CAMPO PARA LA RELACIÓN POLIMÓRFICA CON CATEGORÍAS
                        Select::make('categories')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->label('Categorías')
                    ]),
                Section::make('SIAT')
                    ->schema([
                        Select::make('siat_branch_id')
                            ->label('Punto venta SIAT')
                            ->options(
                                    AmyrConnectionBranch::query()
                                        ->select(
                                            DB::raw("CONCAT(branches.name, ' - ', amyr_connection_branches.sucursal, ' - ', amyr_connection_branches.point_sale) as description"),
                                            'amyr_connection_branches.id'
                                            )
                                        ->join('branches', 'branches.id', '=', 'amyr_connection_branches.branch_id')
                                        ->where('amyr_connection_branches.is_actived', true)
                                        ->whereNotNull('token')
                                        ->where('branches.is_active', true)
                                        ->pluck('description', 'branches.id')
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('siat_data_actividad_code', null);
                                $set('siat_data_product_code', null);
                                $set('siat_data_medida_code', null);
                            }),

                        // 2. SEGUNDO SELECT: Actividad
                        Select::make('siat_data_actividad_code')
                            ->label('Actividad SIAT')
                            ->options(function (Get $get) {
                                $branchId = $get('siat_branch_id');
                                if (!$branchId) {
                                    return [];
                                }
                                $siatApiDocumentoTipoDAO = new SiatApiDocumentoTipoDAO($branchId);
                                return $siatApiDocumentoTipoDAO->getPluck();
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->disabled(fn(Get $get): bool => !$get('siat_branch_id'))
                            ->afterStateUpdated(function (Set $set) {
                                $set('siat_data_product_code', null);
                                $set('siat_data_medida_code', null);
                            })
                            // SOLUCIÓN: Obligatorio si el padre tiene valor
                            ->required(fn(Get $get): bool => filled($get('siat_branch_id'))),

                        // 3. TERCER SELECT: Producto
                        Select::make('siat_data_product_code')
                            ->label('Producto SIAT')
                            ->options(function (Get $get) {
                                $branchId = $get('siat_branch_id');
                                if (!$branchId) {
                                    return [];
                                }
                                $siatApiProductoServicioDAO = new \App\DAOs\SiatApiProductoServicioDAO($branchId);
                                return $siatApiProductoServicioDAO->getPluckByActividad($get('siat_data_actividad_code'));
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->disabled(fn(Get $get): bool => !$get('siat_data_actividad_code'))
                            ->required(fn(Get $get): bool => filled($get('siat_branch_id'))),

                        // 4. CUARTO SELECT: Unidad de Medida
                        Select::make('siat_data_medida_code')
                            ->label('Unidad de Medida SIAT')
                            ->options(
                                function (Get $get) {
                                $branchId = $get('siat_branch_id');
                                if (!$branchId) {
                                    return [];
                                }
                                $siatApiUnidadMedidaDAO = new \App\DAOs\SiatApiUnidadMedidaDAO($branchId);
                                return $siatApiUnidadMedidaDAO->getPluck();
                            })
                            ->searchable()
                            ->preload()
                            ->disabled(fn(Get $get): bool => !$get('siat_data_actividad_code'))
                            // SOLUCIÓN: Obligatorio si el padre principal tiene valor
                            ->required(fn(Get $get): bool => filled($get('siat_branch_id')))
                    ])
            ]);
    }
}
