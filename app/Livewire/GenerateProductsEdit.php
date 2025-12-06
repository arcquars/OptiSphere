<?php

namespace App\Livewire;

use App\DAOs\SiatApiDocumentoTipoDAO;
use App\Http\Requests\StoreGenerateProductsRequest;
use App\Http\Requests\UpdateGenerateProductsRequest;
use App\Models\AmyrConnectionBranch;
use App\Models\OpticalProperty;
use App\Models\Product;
use App\Models\Price;
use App\Models\Supplier;
use App\Models\SiatSucursalPuntoVenta;
use App\Models\SiatDataActividad;
use App\Models\SiatDataProducto;
use App\Models\SiatDataUnidadMedida;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class GenerateProductsEdit extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public $suppliers;

    // Propiedades del formulario manual
    public $baseCode;
    public $supplier;
    public $priceNormal;
    public $priceSpecial;
    public $priceWholesale;

    private $opticalProperty;
    /**
     * @var Product $record
     */
    public $record; // Producto que se está editando

    // Datos del formulario de Filament (SIAT)
    public ?array $data = [];

    public function mount($baseCode)
    {
        $this->suppliers = Supplier::all();

        // Llenar las propiedades de Livewire con los datos actuales
        $this->baseCode = $baseCode;

        $this->opticalProperty = OpticalProperty::where('base_code', $this->baseCode)->firstOrFail();
        $this->record = Product::find($this->opticalProperty->product_id);
        // Cargar precios existentes
        $this->priceNormal = $this->record->prices->where('type', Price::TYPE_NORMAL)->first()?->price;
        $this->priceSpecial = $this->record->prices->where('type', Price::TYPE_ESPECIAL)->first()?->price;
        $this->priceWholesale = $this->record->prices->where('type', Price::TYPE_MAYORISTA)->first()?->price;

        $fill = $this->record->attributesToArray();
        $this->supplier = $this->record->supplier()->first()?->id;
        // Llenar el formulario de Filament con los datos SIAT del registro
        $this->form->fill($fill);
    }

    public function rules()
    {
        // Puedes ajustar las reglas si es necesario para la edición
        return (new UpdateGenerateProductsRequest())->rules();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('SIAT')
                    ->schema([
                        // 1. PRIMER SELECT: Sucursal / Punto de Venta
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
            ])
            ->statePath('data');
    }

    public function update()
    {
        try {
            $this->form->validate();
            $this->validate();
            $siatData = $this->form->getState();
            // Obtener una instancia del producto a actualizar
            $opticalProperties = OpticalProperty::where('base_code', $this->baseCode)->get();
            $opticalProperties->load(['product.prices']);

            // Array para recolectar las actualizaciones masivas de precios
            $pricesToUpdate = [];

            DB::transaction(function () use ($siatData, $opticalProperties, &$pricesToUpdate) {
                // 2. Iterar y recolectar las actualizaciones
                foreach ($opticalProperties as $op) {
                    $productId = $op->product_id; // Asumimos que tienes el product_id en $op

                    // --- A. Actualización Masiva de Productos ---
                    // Llamar a update() dentro del bucle para la tabla Products
                    // NO es ideal, pero si son pocos campos, es aceptable. Si fueran muchos,
                    // deberías recolectar todos los IDs y hacer un solo update fuera del bucle.
                    $op->product->update([
                        'supplier_id' => $this->supplier,
                        'siat_branch_id' => $siatData['siat_branch_id'] ?? null,
                        'siat_data_actividad_code' => $siatData['siat_data_actividad_code'] ?? null,
                        'siat_data_product_code' => $siatData['siat_data_product_code'] ?? null,
                        'siat_data_medida_code' => $siatData['siat_data_medida_code'] ?? null,
                    ]);


                    // --- B. Recolección para Actualización Masiva de Precios (UPSERT) ---

                    $priceConfig = [
                        Price::TYPE_NORMAL => $this->priceNormal,
                        Price::TYPE_ESPECIAL => $this->priceSpecial,
                        Price::TYPE_MAYORISTA => $this->priceWholesale,
                    ];

                    foreach ($priceConfig as $type => $priceValue) {
                        // 3. Revisamos si el precio ya existe en la colección cargada (prices)
                        $existingPrice = $op->product->prices->firstWhere('type', $type);

                        // Si el precio ya existe, lo actualizamos.
                        if ($existingPrice) {
                            // Si el precio existe, actualizamos su valor
                            $existingPrice->price = $priceValue;
                            $existingPrice->save(); // 1 consulta de UPDATE por cada precio existente
                        } else {
                            // Si el precio NO existe, lo agregamos para creación masiva (inserción)
                            $pricesToUpdate[] = [
                                'product_id' => $productId,
                                'type' => $type,
                                'price' => $priceValue,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                }

                // 4. Inserción Masiva de Precios Faltantes (Una sola consulta)
                // Esto insertará todos los precios que no existían.
                if (!empty($pricesToUpdate)) {
                    Price::insert($pricesToUpdate); // ¡Una sola consulta para N inserciones!
                }

            });

            \Filament\Notifications\Notification::make()
                ->title('Producto actualizado correctamente')
                ->success()
                ->send();

        } catch (ValidationException $e) {
            // dd($e->errors()); 
            \Filament\Notifications\Notification::make()
                ->title('Error en validacion')
                ->body($e->getMessage())
                ->danger()
                ->send();

        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('Error al actualizar')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }

        // Recargar la página o redirigir
        // return $this->redirect(..., navigate: true);
    }

    public function render()
    {
        return view('livewire.generate-products-edit');
    }
}
