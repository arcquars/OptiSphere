<?php

namespace App\Livewire\SiatManager;

use Amyrit\SiatBoliviaClient\Exceptions\SiatException;
use App\Livewire\SiatManager\Base\BaseSiat;
use App\Models\SiatDataMetodoPago;
use App\Models\SiatDataTipoEmision;
use App\Services\SiatService;
use DB;
use Log;

class TipoMetodoPagoSiat extends BaseSiat
{
    public function render()
    {
        return view('livewire.siat-manager.tipo-metodo-pago-siat');
    }

    public function loadData(){
        if (isset($this->siatSucursalPuntoVenta)) {
            $this->items = SiatDataMetodoPago::where('siat_spv_id', $this->siatSucursalPuntoVenta->id)->get();
        }
    }

    /**
     * @param SiatService $siatService
     */
    public function getItems(SiatService $siatService){
        DB::beginTransaction();
        try {
            $tipoEmisiones = $siatService->getTipoMetodoPagos($this->siatProperty);
            
            SiatDataMetodoPago::where('siat_spv_id', $this->siatSucursalPuntoVenta->id)->delete();

            $dataToInsert = [];
            $now = now(); // Para usar el mismo timestamp en todos los registros
            $tipoCatalogo = SiatDataMetodoPago::$catalogoType; // Usar la propiedad estática
            
            // 2. CONSTRUIR EL ARRAY DE DATOS
            foreach($tipoEmisiones as $tipoEmision){
                $dataToInsert[] = [
                    // *** Usamos la propiedad estática para asegurar el valor correcto ***
                    "tipo_catalogo"       => $tipoCatalogo, 
                    "codigo_clasificador" => $tipoEmision->codigoClasificador,
                    "descripcion"         => $tipoEmision->descripcion,
                    "siat_spv_id"         => $this->siatSucursalPuntoVenta->id,
                    "created_at"          => $now,
                    "updated_at"          => $now,
                ];
            }

            if (!empty($dataToInsert)) {
                SiatDataMetodoPago::insert($dataToInsert); 
            }
            
            DB::commit();
            $this->loadData();
            \Filament\Notifications\Notification::make()
                ->title('Siat Tipo Metodo Pago')
                ->body('Se actualizaron los Tipo Metodo Pago')
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
