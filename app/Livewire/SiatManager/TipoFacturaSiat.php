<?php

namespace App\Livewire\SiatManager;

use Amyrit\SiatBoliviaClient\Exceptions\SiatException;
use App\Livewire\SiatManager\Base\BaseSiat;
use App\Models\SiatDataPuntoVenta;
use App\Models\SiatDataTipoFactura;
use App\Models\SiatDataUnidadMedida;
use App\Services\SiatService;
use DB;
use Log;


class TipoFacturaSiat extends BaseSiat
{
    public function render()
    {
        return view('livewire.siat-manager.tipo-factura-siat');
    }

    public function loadData(){
        if (isset($this->siatSucursalPuntoVenta)) {
            $this->items = SiatDataTipoFactura::where('siat_spv_id', $this->siatSucursalPuntoVenta->id)->get();
        }
    }

    /**
     * @param SiatService $siatService
     */
    public function getItems(SiatService $siatService){
        DB::beginTransaction();
        try {
            $tipoFacturas = $siatService->getTipoFacturas($this->siatProperty);
            
            SiatDataTipoFactura::where('siat_spv_id', $this->siatSucursalPuntoVenta->id)->delete();

            $dataToInsert = [];
            $now = now(); // Para usar el mismo timestamp en todos los registros
            $tipoCatalogo = SiatDataTipoFactura::$catalogoType; // Usar la propiedad estática
            
            foreach($tipoFacturas as $tipoFactura){
                $dataToInsert[] = [
                    // *** Usamos la propiedad estática para asegurar el valor correcto ***
                    "tipo_catalogo"       => $tipoCatalogo, 
                    "codigo_clasificador" => $tipoFactura->codigoClasificador,
                    "descripcion"         => $tipoFactura->descripcion,
                    "siat_spv_id"         => $this->siatSucursalPuntoVenta->id,
                    "created_at"          => $now,
                    "updated_at"          => $now,
                ];
            }

            if (!empty($dataToInsert)) {
                SiatDataTipoFactura::insert($dataToInsert); 
            }
            
            DB::commit();
            $this->loadData();
            \Filament\Notifications\Notification::make()
                ->title('Siat Tipo Factura')
                ->body('Se actualizaron los Tipos de Factura')
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
