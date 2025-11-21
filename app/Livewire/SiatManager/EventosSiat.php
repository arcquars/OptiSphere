<?php

namespace App\Livewire\SiatManager;

use Amyrit\SiatBoliviaClient\Exceptions\SiatException;
use App\Livewire\SiatManager\Base\BaseSiat;
use App\Models\SiatDataEvento;
use App\Models\SiatDataLeyenda;
use App\Services\SiatService;
use DB;
use Log;

class EventosSiat extends BaseSiat
{

    public function render()
    {
        return view('livewire.siat-manager.eventos-siat');
    }

    public function loadData(){
        if (isset($this->siatSucursalPuntoVenta)) {
            $this->items = SiatDataEvento::where('siat_spv_id', $this->siatSucursalPuntoVenta->id)->get();
        }
    }

    /**
     * @param SiatService $siatService
     */
    public function getItems(SiatService $siatService){
        DB::beginTransaction();
        try {
            $eventos = $siatService->getEventos($this->siatProperty);
            
            SiatDataEvento::where('siat_spv_id', $this->siatSucursalPuntoVenta->id)->delete();

            
            foreach($eventos as $evento){
                SiatDataEvento::create([
                    "codigo_clasificador" => $evento->codigoClasificador,
                    "descripcion" => $evento->descripcion,
                    "siat_spv_id" => $this->siatSucursalPuntoVenta->id
                ]);
            }
            DB::commit();
            $this->loadData();
            \Filament\Notifications\Notification::make()
                ->title('Siat Eventos significativos')
                ->body('Se actualizaron los Eventos significativos')
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
