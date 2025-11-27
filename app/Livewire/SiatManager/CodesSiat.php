<?php

namespace App\Livewire\SiatManager;

use Amyrit\SiatBoliviaClient\Exceptions\SiatException;
use Amyrit\SiatBoliviaClient\SiatConfig;
use App\Models\SiatCufd;
use App\Models\SiatProperty;
use App\Models\SiatSucursalPuntoVenta;
use App\Services\SiatCodigos;
use App\Services\SiatOperaciones;
use App\Services\SiatService;
use Livewire\Component;
use Log;

class CodesSiat extends Component
{
    public $branchId;
    public SiatProperty $siatProperty;
    public SiatSucursalPuntoVenta $siatSucursalPuntoVenta;

    public ?SiatCufd $cufd;
    public function mount($branchId){
        $this->branchId = $branchId;

        $auxSiat = SiatProperty::where('branch_id', $branchId)->first();
        if($auxSiat){
            $this->siatProperty = $auxSiat;
            $this->siatSucursalPuntoVenta = $this->siatProperty->siatSucursalPuntoVentaActive;
            $this->cufd = $this->siatSucursalPuntoVenta->getSiatCufdActive();
        }
    }

    /**
     * @param SiatService $siatService
     */
    public function getCuis(SiatService $siatService){
        try {
            $resultCuis = $siatService->getCuis($this->siatProperty, $this->siatSucursalPuntoVenta->sucursal, $this->siatSucursalPuntoVenta->punto_venta);
            $this->siatSucursalPuntoVenta->cuis = $resultCuis->codigoCuis;
            $this->siatSucursalPuntoVenta->cuis_date = $resultCuis->fechaVigencia;
            $this->siatSucursalPuntoVenta->save();
            \Filament\Notifications\Notification::make()
                ->title('Siat: ' . $resultCuis->mensajesList[0]->codigo)
                ->body($resultCuis->mensajesList[0]->descripcion)
                ->success()
                ->send();
        } catch (SiatException $e) {
            \Filament\Notifications\Notification::make()
                ->title('Error Siat')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('Error General Siat')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * @param SiatCodigos $codigosService
     */
    public function getCufd(SiatCodigos $codigosService){
        try {
            $resultCuis = $codigosService->getCufd($this->siatProperty);
            $cufd = SiatCufd::create([
                'codigo' => $resultCuis->codigo,
                'codigo_control' => $resultCuis->codigoControl,
                'direccion' => $resultCuis->direccion,
                'fecha_vigencia' => $resultCuis->fechaVigencia,
                'siat_spv_id' => $this->siatSucursalPuntoVenta->id,
            ]);
            $this->cufd = $cufd;

            $message = (count($resultCuis->mensajesList) > 0) ? $resultCuis->mensajesList[0]->descripcion : 'CUFD obtenido correctamente';
            \Filament\Notifications\Notification::make()
                ->title($message)
                ->body('Siat: ' . $resultCuis->codigo)
                ->success()
                ->send();
        } catch (SiatException $e) {
            \Filament\Notifications\Notification::make()
                ->title('Error Siat')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('Error General Siat')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.siat-manager.codes-siat');
    }
}
