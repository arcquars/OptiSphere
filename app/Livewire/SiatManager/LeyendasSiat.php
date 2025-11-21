<?php

namespace App\Livewire\SiatManager;

use Amyrit\SiatBoliviaClient\Exceptions\SiatException;
use App\Livewire\SiatManager\Base\BaseSiat;
use App\Models\SiatDataLeyenda;
use App\Services\SiatService;
use DB;
use Log;

class LeyendasSiat extends BaseSiat
{

    public function render()
    {
        return view('livewire.siat-manager.leyendas-siat');
    }

    public function loadData(){
        if (isset($this->siatSucursalPuntoVenta)) {
            $this->items = SiatDataLeyenda::where('siat_spv_id', $this->siatSucursalPuntoVenta->id)->get();
        }
    }

    /**
     * @param SiatService $siatService
     */
    public function getItems(SiatService $siatService){
        DB::beginTransaction();
        try {
            $leyendas = $siatService->getActividadesLeyendas($this->siatProperty);
            
            SiatDataLeyenda::where('siat_spv_id', $this->siatSucursalPuntoVenta->id)->delete();

            
            foreach($leyendas as $leyenda){
                SiatDataLeyenda::create([
                    "codigo_actividad" => $leyenda->codigoActividad,
                    "descripcion_leyenda" => $leyenda->descripcionLeyenda,
                    "siat_spv_id" => $this->siatSucursalPuntoVenta->id
                ]);
            }
            DB::commit();
            $this->loadData();
            \Filament\Notifications\Notification::make()
                ->title('Siat Leyendas factura')
                ->body('Se actualizaron las Leyendas')
                ->success()
                ->send();
        } catch (SiatException $e) {
            Log::error($e->getMessage());
            \Filament\Notifications\Notification::make()
                ->title('Error Siat')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            \Filament\Notifications\Notification::make()
                ->title('Error General Siat: ' . $e->getCode())
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
