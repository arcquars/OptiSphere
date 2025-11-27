<?php

namespace App\Livewire\SiatManager;

use Amyrit\SiatBoliviaClient\Exceptions\SiatException;
use App\Livewire\SiatManager\Base\BaseSiat;
use App\Models\SiatDataPuntoVenta;
use App\Models\SiatDataUnidadMedida;
use App\Services\SiatService;
use DB;
use Log;

class UnidadMedidaSiat extends BaseSiat
{
    public function render()
    {
        return view('livewire.siat-manager.unidad-medida-siat');
    }

    public function loadData(){
        if (isset($this->siatSucursalPuntoVenta)) {
            $this->items = SiatDataUnidadMedida::where('siat_spv_id', $this->siatSucursalPuntoVenta->id)->get();
        }
    }

    /**
     * @param SiatService $siatService
     */
    public function getItems(SiatService $siatService){
        DB::beginTransaction();
        try {
            $unidadMedidas = $siatService->getUnidadMedidas($this->siatProperty);
            
            SiatDataPuntoVenta::where('siat_spv_id', $this->siatSucursalPuntoVenta->id)->delete();

            $dataToInsert = [];
            $now = now(); // Para usar el mismo timestamp en todos los registros
            $tipoCatalogo = SiatDataUnidadMedida::$catalogoType; // Usar la propiedad estática
            
            foreach($unidadMedidas as $unidadMedida){
                $dataToInsert[] = [
                    // *** Usamos la propiedad estática para asegurar el valor correcto ***
                    "tipo_catalogo"       => $tipoCatalogo, 
                    "codigo_clasificador" => $unidadMedida->codigoClasificador,
                    "descripcion"         => $unidadMedida->descripcion,
                    "siat_spv_id"         => $this->siatSucursalPuntoVenta->id,
                    "created_at"          => $now,
                    "updated_at"          => $now,
                ];
            }

            if (!empty($dataToInsert)) {
                SiatDataUnidadMedida::insert($dataToInsert); 
            }
            
            DB::commit();
            $this->loadData();
            \Filament\Notifications\Notification::make()
                ->title('Siat Unidad Medida')
                ->body('Se actualizaron las Unidades de Medida')
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
