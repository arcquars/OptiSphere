<?php

namespace App\Livewire\SiatManager;

use Amyrit\SiatBoliviaClient\Exceptions\SiatException;
use App\Livewire\SiatManager\Base\BaseSiat;
use App\Models\SiatDataMotivoAnulacion;
use App\Services\SiatService;
use DB;
use Log;

class MotivoAnulacionSiat extends BaseSiat
{
    public function render()
    {
        return view('livewire.siat-manager.motivo-anulacion-siat');
    }

    public function loadData(){
        if (isset($this->siatSucursalPuntoVenta)) {
            $this->items = SiatDataMotivoAnulacion::where('siat_spv_id', $this->siatSucursalPuntoVenta->id)->get();
        }
    }

    /**
     * @param SiatService $siatService
     */
    public function getItems(SiatService $siatService){
        DB::beginTransaction();
        try {
            $motivoAnulaciones = $siatService->getMotivoAnulaciones($this->siatProperty);
            
            SiatDataMotivoAnulacion::where('siat_spv_id', $this->siatSucursalPuntoVenta->id)->delete();

            
            foreach($motivoAnulaciones as $motivoAnulacion){
                SiatDataMotivoAnulacion::create([
                    "codigo_clasificador" => $motivoAnulacion->codigoClasificador,
                    "descripcion" => $motivoAnulacion->descripcion,
                    "siat_spv_id" => $this->siatSucursalPuntoVenta->id
                ]);
            }
            DB::commit();
            $this->loadData();
            \Filament\Notifications\Notification::make()
                ->title('Siat Motivo anulaciones')
                ->body('Se actualizaron los motivos anulaciones')
                ->success()
                ->send();
        } catch (SiatException $e) {
            Log::error($e->getMessage());
            \Filament\Notifications\Notification::make()
                ->title('Error Siat')
                ->body($e->getMessage())
                ->danger()
                ->send();
            DB::rollBack();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            \Filament\Notifications\Notification::make()
                ->title('Error General Siat: ' . $e->getCode())
                ->body($e->getMessage())
                ->danger()
                ->send();
                DB::rollBack();
        }
    }
}
