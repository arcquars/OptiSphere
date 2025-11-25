<?php

namespace App\Livewire\SiatManager;

use Amyrit\SiatBoliviaClient\Exceptions\SiatException;
use App\Livewire\SiatManager\Base\BaseSiat;
use App\Models\SiatDataDocIdentidadTipo;
use App\Models\SiatDataLeyenda;
use App\Services\SiatService;
use DB;
use Log;

class DocumentosIdentidadSiat extends BaseSiat
{
    public function render()
    {
        return view('livewire.siat-manager.documentos-identidad-siat');
    }

    public function loadData(){
        if (isset($this->siatSucursalPuntoVenta)) {
            $this->items = SiatDataDocIdentidadTipo::where('siat_spv_id', $this->siatSucursalPuntoVenta->id)->get();
        }
    }

    /**
     * @param SiatService $siatService
     */
    public function getItems(SiatService $siatService)
    {
        DB::beginTransaction();
        try {
            $documentosIdentidad = $siatService->getDocumentosIdentidad($this->siatProperty);
            
            // 1. ELIMINAR REGISTROS ANTERIORES
            // Esto es necesario ya que vamos a insertar la lista completa de nuevo.
            SiatDataDocIdentidadTipo::where('siat_spv_id', $this->siatSucursalPuntoVenta->id)->delete();

            $dataToInsert = [];
            $now = now(); // Para usar el mismo timestamp en todos los registros
            $tipoCatalogo = SiatDataDocIdentidadTipo::$catalogoType; // Usar la propiedad estática
            
            // 2. CONSTRUIR EL ARRAY DE DATOS
            foreach($documentosIdentidad as $documentoIdentidad){
                $dataToInsert[] = [
                    // *** Usamos la propiedad estática para asegurar el valor correcto ***
                    "tipo_catalogo"       => $tipoCatalogo, 
                    "codigo_clasificador" => $documentoIdentidad->codigoClasificador,
                    "descripcion"         => $documentoIdentidad->descripcion,
                    "siat_spv_id"         => $this->siatSucursalPuntoVenta->id,
                    "created_at"          => $now,
                    "updated_at"          => $now,
                ];
            }

            // 3. INSERCIÓN MASIVA (Más rápido y evita el método create())
            // Usamos el modelo base para el método `insert` si la tabla es 'siat_datas'
            if (!empty($dataToInsert)) {
                // Nota: Aquí se usa SiatDataDocIdentidadTipo::insert() o SiatData::insert()
                // dependiendo de dónde se encuentre el trait HasTable. Ambos funcionan si la tabla es la misma.
                SiatDataDocIdentidadTipo::insert($dataToInsert); 
            }

            DB::commit();
            $this->loadData();
            \Filament\Notifications\Notification::make()
                ->title('Siat Tipos documento identidad')
                ->body('Se actualizaron los tipos de documento de identidad')
                ->success()
                ->send();
        } catch (SiatException $e) {
            DB::rollBack();
            Log::error("Error Siat en getItems: " . $e->getMessage());
            \Filament\Notifications\Notification::make()
                ->title('Error Siat')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error General en getItems: " . $e->getMessage());
            \Filament\Notifications\Notification::make()
                ->title('Error General Siat: ' . $e->getCode())
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
