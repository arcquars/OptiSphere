<?php

namespace App\Filament\Resources\Products\Schemas;

use App\DAOs\SiatApiDocumentoTipoDAO;
use App\Models\AmyrConnectionBranch;
use App\Models\BaseCode;
use App\Models\Branch;
use App\Models\Product;
use App\Models\SiatDataActividad;
use App\Models\SiatDataProducto;
use App\Models\SiatDataUnidadMedida;
use App\Models\SiatSucursalPuntoVenta;
use DB;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Model;


class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('Producto')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre del Producto')
                            ->required(),
                        TextInput::make('code')
                            ->label('Codigo')
                            ->required()
                            ->rule(function (Get $get, ?Model $record): \Illuminate\Validation\Rules\Unique {
                                // 2. Construimos la regla 'unique' aquí dentro
                                $rule = Rule::unique('products', 'code')
                                    ->withoutTrashed();

                                // 3. Le decimos a la regla qué ID ignorar (solo al editar)
                                if ($record) {
                                    $rule->ignore($record->id);
                                }
                                return $rule;
                            }),
                        Select::make('supplier_id')
                            ->label('Proveedor')
                            ->relationship(name: 'supplier', titleAttribute: 'name')
                            ->searchable(['name'])
                            ->preload(),
                        Toggle::make('is_active')
                            ->label('Estado Activo')
                            ->default(true)
                            ->required()
                            ->disabled(
                                fn (Get $get): bool =>
                                !empty($get('has_optical_properties'))
                            ),
                        FileUpload::make('image_path')
                            ->label('Imagen')
                            ->image()
                            ->disk('public')
                            ->directory('product-attachments')
                            ->visibility('public')
                            // ->required()
                            ->columnSpan(2),
                        Textarea::make('description')
                            ->label('Descripcion')
                            ->required()
                            ->columnSpan(2),

                        ])->columns(4),
                Select::make('categories')
                    ->label('Categorías')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->label('Categorías')
                    ->columnSpan('full'),
                Checkbox::make('has_optical_properties')
                    ->label('¿Agregar Propiedades Ópticas?')
                    ->live() // 'reactive()' se llama 'live()' en v4
                    // Este hook personaliza cómo se "hidrata" (carga) el estado
                    // del checkbox al abrir la página de "Editar".
                    ->afterStateHydrated(function (Component $component, ?Product $record) {
                        // $record es el Product que se está editando.
                        if ($record === null) {
                            return; // Estamos en "Crear", no hacer nada.
                        }

                        // Esta es la consulta Eloquent
                        if ($record->opticalProperties()->exists()) {
                            // Usamos $component->state() para establecer el estado.
                            $component->state(true);
                        }
                    }),
                Section::make('Propiedades Ópticas')
                    ->visible(fn (Get $get) => $get('has_optical_properties'))
                    ->schema([
                        // Agrega el Fieldset para la tabla optical_properties
                        Fieldset::make('Propiedades Opticas')
                            ->schema([
                                Select::make('base_code')
                                    ->label('Código Base')
                                    ->options(BaseCode::all()->pluck('name', 'name'))
                                    ->required()
                                    ->searchable(),
                                Radio::make('type')
                                    ->label('Tipo?')
                                    ->options([
                                        '+' => 'Positivo',
                                        '-' => 'Negativo',
                                    ])
                                    ->default('+')
                                    ->inline(),
                                TextInput::make('sphere')
                                    ->label('Esfera')
                                    ->numeric()
                                    ->required(),
                                TextInput::make('cylinder')
                                    ->label('Cilindro')
                                    ->numeric()
                                    ->required(),
                            ])
                            ->relationship('opticalProperties'),
                    ]),

                // Repeater para los diferentes tipos de precios
                // Repeater para los diferentes tipos de precios
                Repeater::make('prices')
                    ->label('Precios por Tipo de Cliente')
                    ->relationship('prices')
                    ->schema([
                        Select::make('type')
                            ->label('Tipo de cliente')
                            ->options([
                                'normal' => 'Normal',
                                'especial' => 'Especial',
                                'mayorista' => 'Mayorista',
                            ])
                            ->required(),
//                            ->unique(ignoreRecord: true), // Asegura que cada tipo de cliente sea único
                        TextInput::make('price')
                            ->label('Precio')
                            ->numeric()
                            ->required(),
                    ])
                    ->columns(2) // Muestra los campos en dos columnas
                    ->collapsible() // Permite colapsar los bloques de precio
                    ->defaultItems(3) // Genera 3 campos por defecto (uno para cada tipo)
                    ->minItems(3) // Hace que los 3 elementos sean obligatorios
                    ->maxItems(3), // No permite agregar más de 3 elementos
                

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
                            // SOLUCIÓN: Obligatorio si el padre principal tiene valor
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




            ])->columns(1);
    }
}
