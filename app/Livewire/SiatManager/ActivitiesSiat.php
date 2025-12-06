<?php

namespace App\Livewire\SiatManager;

use Amyrit\SiatBoliviaClient\Exceptions\SiatException;
use App\Livewire\SiatManager\Base\BaseSiat;
use App\Models\SiatDataActividad;
use App\Models\SiatProperty;
use App\Models\SiatSucursalPuntoVenta;
use App\Services\AmyrCatalogsService;
use App\Services\SiatService;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Log;

class ActivitiesSiat extends BaseSiat
{
    public function loadData(){
        $this->items = SiatDataActividad::where('siat_spv_id', $this->siatSucursalPuntoVenta->id)->get();
    }

    /**
     * @param SiatService $siatService
     */
    public function getItems(SiatService $siatService){
        DB::beginTransaction();
        try {
            Log::info("pppppp:: ");
            $actividades = $siatService->getActividades($this->siatProperty);
            Log::info("pppppp:: ");
            $i = 1;
            SiatDataActividad::where('siat_spv_id', $this->siatSucursalPuntoVenta->id)->delete();

            foreach($actividades as $actividad){
                SiatDataActividad::create([
                    "nro" => $i++,
                    "codigo" => $actividad->codigoCaeb,
                    "descripcion" => $actividad->descripcion,
                    "tipo" => $actividad->tipo?? "",
                    "siat_spv_id" => $this->siatSucursalPuntoVenta->id
                ]);
            }
            DB::commit();
            $this->loadData();
            \Filament\Notifications\Notification::make()
                ->title('Siat Actividades')
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

    /**
     * @param AmyrCatalogsService $amyrCatalogsService
     */
    public function getApiItems(AmyrCatalogsService $amyrCatalogsService){
        $amyrConnectedBranch = $this->siatProperty->branch->amyrConnectionBranch;
        DB::beginTransaction();
        try {
            Log::info("pppppp1:: ");
            $amyrCatalogsService->setToken($amyrConnectedBranch->token);
            $actividades = $amyrCatalogsService->getActividades();
            Log::info("pppppp2:: " . json_encode($actividades['RespuestaListaActividades']['listaActividades']));
            if($actividades['RespuestaListaActividades']['transaccion']){
                $i = 1;
                SiatDataActividad::where('siat_spv_id', $this->siatSucursalPuntoVenta->id)->delete();

                foreach($actividades['RespuestaListaActividades']['listaActividades'] as $actividad){
                    SiatDataActividad::create([
                        "nro" => $i++,
                        "codigo" => $actividad['codigoCaeb'],
                        "descripcion" => $actividad['descripcion'],
                        "tipo" => $actividad['tipoActividad']?? "",
                        "siat_spv_id" => $this->siatSucursalPuntoVenta->id
                    ]);
                }

                DB::commit();
                $this->loadData();
                \Filament\Notifications\Notification::make()
                    ->title('Siat Actividades')
                    ->body('Se actualizaron las actividades')
                    ->success()
                    ->send();
            } else {
                \Filament\Notifications\Notification::make()
                    ->title('Error Api Amyr')
                    ->body("Respuesta no exitosa de la API de Amyr")
                    ->danger()
                    ->send();
            }
            
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
        return view('livewire.siat-manager.activities-siat');
    }
}
