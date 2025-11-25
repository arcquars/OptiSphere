<?php

namespace App\Filament\Resources\Branches\Pages;

use Amyrit\SiatBoliviaClient\Data\Requests\SolicitudSincronizacion;
use Amyrit\SiatBoliviaClient\Exceptions\SiatException;
use Amyrit\SiatBoliviaClient\SiatClient;
use App\Filament\Resources\Branches\BranchResource;
use App\Models\Branch;
use App\Models\SiatSucursalPuntoVenta;
use App\Services\SiatService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use App\Models\SiatProperty;
// use Filament\Schemas\Components\Form;
use Amyrit\SiatBoliviaClient\SiatConfig;
use Log;

class InvoiceConfig extends Page implements HasSchemas
{
    use InteractsWithSchemas;
    
    public ?array $data = [];

    protected static string $resource = BranchResource::class;

    // protected static ?string $title = 'Configurar SIAT';

    protected string $view = 'filament.resources.branches.pages.invoice-config';

    public Branch $branch;

    public ?SiatProperty $siatProperty = null;

    public function mount(int $branch_id): void
    {
        $this->branch = Branch::find($branch_id);

        $this->siatProperty = $this->branch->siatProperty ?? $this->branch->siatProperty()->make();

        $datas = $this->siatProperty->attributesToArray();
        if($this->siatProperty->siatSucursalPuntoVentaActive){
            $datas = array_merge($datas, [
                'sucursal' => $this->siatProperty->siatSucursalPuntoVentaActive->sucursal, 
                'point_sale' => $this->siatProperty->siatSucursalPuntoVentaActive->punto_venta]
            );
        }

        $this->form->fill($datas);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns([
                'sm' => 1,
                'xl' => 2,
                '2xl' => 4,
            ])
            ->components([
                TextInput::make('system_name')
                    ->label('Nombre del sistema')
                    ->required(),
                TextInput::make('system_code')
                    ->label('Código del sistema')
                    ->required(),
                TextInput::make('nit')
                    ->label('NIT')
                    ->required(),
                TextInput::make('company_name')
                    ->label('Razón social')
                    ->required(),
                Select::make('modality')
                    ->label('Modalidad')
                    ->options([
                        '1' => 'Electronica en linea',
                        '2' => 'Computarizada en linea',
                    ])
                    ->required(),
                Select::make('environment')
                    ->label('Ambiente')
                    ->options([
                        '1' => 'Produccion',
                        '2' => 'Pruebas y Piloto',
                    ])
                    ->required(),
                TextInput::make('city')
                    ->label('Ciudad')
                    ->required(),
                TextInput::make('phone')
                    ->label('Teléfono'),
                TextInput::make('token')
                    ->columnSpan('4')
                    ->label('Token delegado')
                    ->required(),
                TextInput::make('cafc')
                    ->label('CAFC'),
                TextInput::make('cafc_ini')
                    ->label('CAFC Nro Inicio de factura'),
                TextInput::make('cafc_end')
                    ->label('CAFC Nro Fin de Factura'),
                Select::make('print_size')
                    ->label('Tipo de impresion')
                    ->options([
                        'page' => 'Media Pagina',
                        'rollo' => 'Ticket/Rollo',
                    ])
                    ->required(),
                TextInput::make('sucursal')
                    ->label('Sucursal')
                    ->integer()->minValue(0)
                    ->maxValue(1)
                    ->required(),
                TextInput::make('point_sale')
                    ->label('Punto de venta')
                    ->integer()->minValue(0)
                    ->maxValue(4)
                    ->required(),
                FileUpload::make('path_digital_signature')
                    ->label('Firma digital (.pem)')
                    ->disk('public')
                    ->directory('keys/siat/' . $this->branch->id)
                    // 1. FRONTEND: Le decimos al navegador qué mostrar (filtro visual)
                    // ->extraInputAttributes(['accept' => '.pem'])
                    // 2. BACKEND: Validamos SOLO por extensión, ignorando el tipo MIME confuso
                    ->rules(['file', 'extensions:pem']),
                Fieldset::make('Estado y Validación')
                ->schema([
                    Toggle::make('is_actived')
                        ->label('Activo'),
                        
                    Toggle::make('is_validated')
                        ->label('Validado Por Impuestos Internos')
                        ->disabled()
                ])->columns(1),
                FileUpload::make('path_logo')
                    ->label('Logo')
                    ->disk('public')
                    ->directory('logos/siat/' . $this->branch->id)
                    ->image(),
            ])
            ->statePath('data')
            ->model($this->siatProperty);
    }
    
    public function submit(): void
    {
        // dd($this->form->getState());
        try {
            // Llenar la propiedad 'siatProperty' con los datos validados del formulario
            $data = $this->form->getState();
            $this->siatProperty->fill($data);

            // Asegurar que branch_id esté seteado en la primera creación
            if (!$this->siatProperty->exists) {
                $this->siatProperty->branch_id = $this->branch->id;
            }

            $this->siatProperty->save();

            $siatSucursalPuntoVenta = SiatSucursalPuntoVenta::
                where('siat_property_id', $this->siatProperty->id)
                ->where('sucursal', $data['sucursal'])
                ->where('punto_venta', $data['point_sale'])->first();
            if($siatSucursalPuntoVenta){
                $siatSucursalPuntoVenta->sucursal = $data['sucursal'];
                $siatSucursalPuntoVenta->punto_venta = $data['point_sale'];
                
            } else {
                $siatSucursalPuntoVenta = new SiatSucursalPuntoVenta();
                $siatSucursalPuntoVenta->siat_property_id = $this->siatProperty->id;
                $siatSucursalPuntoVenta->sucursal = $data['sucursal'];
                $siatSucursalPuntoVenta->punto_venta = $data['point_sale'];
            }
            $siatSucursalPuntoVenta->active = true;
            $siatSucursalPuntoVenta->save();
            // Colocamos el active false en los demas siat_sucursales_puntos_ventas
            SiatSucursalPuntoVenta::where('siat_property_id', $this->siatProperty->id)
                ->where('id', "<>", $siatSucursalPuntoVenta->id)->update(['active' => false]);
            
            \Filament\Notifications\Notification::make()
                ->title('Configuración SIAT guardada')
                ->success()
                ->send();

        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('Error al guardar')
                ->body('Ocurrió un error al guardar la configuración: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * @param SiatService $siatService
     */
    public function validateSiat(SiatService $siatService): void{
        $isValid = $siatService->validConfig($this->siatProperty);
        $this->siatProperty->is_validated = $isValid;
        $this->siatProperty->save();
        if($isValid) {
            \Filament\Notifications\Notification::make()
                ->title('Configuración SIAT Valida')
                ->success()
                ->send();
        } else {
            \Filament\Notifications\Notification::make()
                ->title('Configuración SIAT No Valida')
                ->danger()
                ->send();
        }
    }

    protected function getViewData(): array
    {
        return [
            'branch' => $this->branch,
        ];
    }

    public function getTitle(): string
    {
        return "{$this->branch->name} - Configurar SIAT";
    }
}
