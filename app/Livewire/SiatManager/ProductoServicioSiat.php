<?php

namespace App\Livewire\SiatManager;

use Amyrit\SiatBoliviaClient\Exceptions\SiatException;
use App\Livewire\SiatManager\Base\BaseSiat;
use App\Models\SiatDataLeyenda;
use App\Models\SiatDataProducto;
use App\Services\SiatService;
use DB;
use Log;

class ProductoServicioSiat extends BaseSiat
{

    public function loadData(){
        if (isset($this->siatSucursalPuntoVenta)) {
            $this->items = SiatDataProducto::where('siat_spv_id', $this->siatSucursalPuntoVenta->id)->get();
        }
    }

    /**
     * @param SiatService $siatService
     */
    public function getItems(SiatService $siatService){
        DB::beginTransaction();
        try {
            $productos = $siatService->getProductos($this->siatProperty);
            
            SiatDataProducto::where('siat_spv_id', $this->siatSucursalPuntoVenta->id)->delete();

            
            foreach($productos as $producto){
                SiatDataProducto::create([
                    "codigo_actividad" => $producto->codigoActividad,
                    "codigo_producto" => $producto->codigoProducto,
                    "descripcion_producto" => $producto->descripcionProducto,
                    "siat_spv_id" => $this->siatSucursalPuntoVenta->id
                ]);
            }
            DB::commit();
            $this->loadData();
            \Filament\Notifications\Notification::make()
                ->title('Siat Productos - Servicios')
                ->body('Se actualizaron los Productos - Servicios')
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
    public function render()
    {
        return view('livewire.siat-manager.producto-servicio-siat');
    }
}
