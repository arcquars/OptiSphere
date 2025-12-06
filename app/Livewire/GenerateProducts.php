<?php

namespace App\Livewire;

use App\DAOs\SiatApiDocumentoTipoDAO;
use App\Helpers\GenerateProductHelper;
use App\Http\Requests\StoreGenerateProductsRequest;
use App\Models\AmyrConnectionBranch;
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
                        }
                    )
                    ->searchable()
                    ->preload()
                    ->disabled(fn(Get $get): bool => !$get('siat_data_actividad_code'))
                    // SOLUCIÓN: Obligatorio si el padre principal tiene valor
                    ->required(fn(Get $get): bool => filled($get('siat_branch_id')))
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

                    if (!empty($siatData['siat_branch_id'])) {
                        $productData['siat_branch_id'] = $siatData['siat_branch_id'];
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