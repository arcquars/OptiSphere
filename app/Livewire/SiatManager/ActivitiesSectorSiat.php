<?php

namespace App\Livewire\SiatManager;

use Amyrit\SiatBoliviaClient\Exceptions\SiatException;
use App\Livewire\SiatManager\Base\BaseSiat;
use App\Models\SiatDataActividadDocSector;
use App\Services\SiatService;
use DB;
use Log;

class ActivitiesSectorSiat extends BaseSiat
{
    public function loadData()
    {
        if (isset($this->siatSucursalPuntoVenta)) {
            $this->items = SiatDataActividadDocSector::where('siat_spv_id', $this->siatSucursalPuntoVenta->id)->get();
        }
    }

    /**
     * @param SiatService $siatService
     */
    public function getItems(SiatService $siatService){
        DB::beginTransaction();
        try {
            $actividades = $siatService->getActividadesDocumentoSector($this->siatProperty);
            SiatDataActividadDocSector::where('siat_spv_id', $this->siatSucursalPuntoVenta->id)->delete();

            
            foreach($actividades as $actividad){
                SiatDataActividadDocSector::create([
                    "codigo_actividad" => $actividad->codigoActividad,
                    "codigo_documento_sector" => $actividad->codigoDocumentoSector,
                    "tipo_documento_sector" => $actividad->tipoDocumentoSector,
                    "siat_spv_id" => $this->siatSucursalPuntoVenta->id
                ]);
            }
            DB::commit();
            $this->loadData();
            \Filament\Notifications\Notification::make()
                ->title('Siat Actividades Documento Sector')
                ->body('Se actualizaron las actividades')
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

    public function render()
    {
        return view('livewire.siat-manager.activities-sector-siat');
    }
}
