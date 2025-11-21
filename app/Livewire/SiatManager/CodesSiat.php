<?php

namespace App\Livewire\SiatManager;

use Amyrit\SiatBoliviaClient\Exceptions\SiatException;
use Amyrit\SiatBoliviaClient\SiatConfig;
use App\Models\SiatProperty;
use App\Models\SiatSucursalPuntoVenta;
use App\Services\SiatService;
use Livewire\Component;
use Log;

class CodesSiat extends Component
{
    public $branchId;
    public SiatProperty $siatProperty;
    public SiatSucursalPuntoVenta $siatSucursalPuntoVenta;
    public function mount($branchId){
        $this->branchId = $branchId;

        $auxSiat = SiatProperty::where('branch_id', $branchId)->first();
        if($auxSiat){
            $this->siatProperty = $auxSiat;
            $this->siatSucursalPuntoVenta = $this->siatProperty->siatSucursalPuntoVentaActive;
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

    public function render()
    {
        return view('livewire.siat-manager.codes-siat');
    }
}
