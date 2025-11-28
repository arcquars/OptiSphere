<?php

namespace App\Livewire;

use App\Helpers\GenerateProductHelper;
use App\Http\Requests\StoreGenerateProductsRequest;
use App\Models\Product;
use App\Models\Price;
use App\Models\Supplier;
use App\Models\SiatSucursalPuntoVenta;
use App\Models\SiatDataActividad;
use App\Models\SiatDataProducto;
use App\Models\SiatDataUnidadMedida;
use Filament\Forms\Components\Select;
// 1. CAMBIO DE NAMESPACES A SCHEMAS (V4 Style)
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class GenerateProducts extends Component implements HasSchemas // <--- Implementa HasSchemas
{
    use InteractsWithSchemas; // <--- Usa InteractsWithSchemas

    public $suppliers;

    public $baseCode;
    public $supplier;
    public $priceNormal;
    public $priceSpecial;
    public $priceWholesale;

    public ?array $data = [];

    public function mount()
    {
        $this->suppliers = Supplier::all();
        $this->form->fill();
    }

    public function rules()
    {
        return (new StoreGenerateProductsRequest())->rules();
    }

    // 2. CAMBIO DE FIRMA: Recibe Schema y devuelve Schema
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([ // 3. El objeto Schema usa 'components', no 'schema' en la raíz
                Section::make('SIAT')
                    ->schema([ // Section sigue usando 'schema' para sus hijos
                        // 1. PRIMER SELECT: Sucursal / Punto de Venta
                        Select::make('siat_sucursal_punto_venta_id')
                            ->label('Punto venta SIAT')
                            ->options(
                                SiatSucursalPuntoVenta::query()
                                    ->select([
                                        DB::raw("CONCAT(branches.name, ' - ', siat_sucursales_puntos_ventas.sucursal, ' - ', siat_sucursales_puntos_ventas.punto_venta) as description"),
                                        'siat_sucursales_puntos_ventas.id'
                                    ])
                                    ->join('siat_properties', 'siat_sucursales_puntos_ventas.siat_property_id', '=', 'siat_properties.id')
                                    ->join('branches', 'siat_properties.branch_id', '=', 'branches.id')
                                    ->where('siat_sucursales_puntos_ventas.active', true)
                                    ->where('branches.is_active', true)
                                    ->pluck('description', 'siat_sucursales_puntos_ventas.id')
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
                                $sucursalPuntoVentaId = $get('siat_sucursal_punto_venta_id');
                                if (!$sucursalPuntoVentaId) {
                                    return [];
                                }
                                return SiatDataActividad::query()
                                    ->where('siat_spv_id', $sucursalPuntoVentaId)
                                    ->pluck('descripcion', 'codigo');
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->disabled(fn(Get $get): bool => !$get('siat_sucursal_punto_venta_id'))
                            ->afterStateUpdated(function (Set $set) {
                                $set('siat_data_product_code', null);
                                $set('siat_data_medida_code', null);
                            })
                            ->required(fn(Get $get): bool => filled($get('siat_sucursal_punto_venta_id'))),

                        // 3. TERCER SELECT: Producto
                        Select::make('siat_data_product_code')
                            ->label('Producto SIAT')
                            ->options(function (Get $get) {
                                $sucursalPuntoVentaId = $get('siat_sucursal_punto_venta_id');
                                if (!$sucursalPuntoVentaId) {
                                    return [];
                                }
                                return SiatDataProducto::query()
                                    ->where('siat_spv_id', $sucursalPuntoVentaId)
                                    ->where('codigo_actividad', $get('siat_data_actividad_code'))
                                    ->pluck('descripcion_producto', 'codigo_producto');
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->disabled(fn(Get $get): bool => !$get('siat_data_actividad_code'))
                            ->required(fn(Get $get): bool => filled($get('siat_sucursal_punto_venta_id'))),

                        // 4. CUARTO SELECT: Unidad de Medida
                        Select::make('siat_data_medida_code')
                            ->label('Unidad de Medida SIAT')
                            ->options(function (Get $get) {
                                $sucursalPuntoVentaId = $get('siat_sucursal_punto_venta_id');
                                if (!$sucursalPuntoVentaId) {
                                    return [];
                                }
                                return SiatDataUnidadMedida::query()
                                    ->where('siat_spv_id', $sucursalPuntoVentaId)
                                    ->pluck('descripcion', 'codigo_clasificador');
                            })
                            ->searchable()
                            ->preload()
                            ->disabled(fn(Get $get): bool => !$get('siat_data_actividad_code'))
                            ->required(fn(Get $get): bool => filled($get('siat_sucursal_punto_venta_id')))
                    ])
            ])
            ->statePath('data');
    }

    public function generateProducts()
    {
        $validateData = $this->validate();
        
        // Obtener los datos del formulario (Schema)
        $siatData = $this->form->getState(); 

        $productsToCreate = GenerateProductHelper::generateZeroToSix(strtoupper($this->baseCode), $this->supplier);
        
        $price1 = ["type" => Price::TYPE_NORMAL, "price" => $this->priceNormal];
        $price2 = ["type" => Price::TYPE_ESPECIAL, "price" => $this->priceSpecial ?: $this->priceNormal];
        $price3 = ["type" => Price::TYPE_MAYORISTA, "price" => $this->priceWholesale ?: $this->priceNormal];

        try {
            DB::transaction(function () use ($productsToCreate, $price1, $price2, $price3, $siatData) {
                foreach ($productsToCreate as $productData) {
                    $opticalPropertiesData = $productData['opticalProperties'];
                    unset($productData['opticalProperties']);

                    if (!empty($siatData['siat_sucursal_punto_venta_id'])) {
                        $productData['siat_sucursal_punto_venta_id'] = $siatData['siat_sucursal_punto_venta_id'];
                        $productData['siat_data_actividad_code'] = $siatData['siat_data_actividad_code'];
                        $productData['siat_data_product_code'] = $siatData['siat_data_product_code'];
                        $productData['siat_data_medida_code'] = $siatData['siat_data_medida_code'];
                    }

                    $product = Product::create($productData);

                    $product->opticalProperties()->create($opticalPropertiesData);
                    $product->prices()->create($price1);
                    $product->prices()->create($price2);
                    $product->prices()->create($price3);
                }
            });
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

        return $this->redirect(route('filament.admin.resources.products.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.generate-products');
    }
}