<?php

namespace App\Livewire\Branch;

use App\DTOs\EventSiatDto;
use App\Models\Branch;
use App\Services\AmyrEventsApiService;
use App\Services\MonoInvoiceApiService;
use Livewire\Component;
use Filament\Notifications\Notification;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

class SiatConnectionStatus extends Component
{
    public Branch $branch;
    
    // Estado de la conexión
    public bool $isOnline = true;
    public bool $checking = false;

    // Estado de Contingencia
    public bool $hasActiveContingency = false;
    public ?string $eventId = null;
    public ?string $eventStartTime = null;

    public ?string $eventDescription = null;

    public Array $eventosSiat = [];
    // Modal
    public bool $showModal = false;

    public $reason = ''; // Motivo de contingencia

    public function mount(Branch $branch)
    {
        $this->branch = $branch;
        $this->eventosSiat = config('amyr.eventos_siat');
        $this->checkStatus();
    }

    /**
     * Esta función se ejecuta automáticamente por el wire:poll
     */
    public function checkStatus()
    {
        try {
            $service = new MonoInvoiceApiService($this->branch);
            $serviceEvent = new AmyrEventsApiService($this->branch->amyrConnectionBranch->token);
            // 1. Verificar conexión
            // Nota: Es ideal que 'verificarOnlineSiat' tenga un timeout bajo en el servicio 
            // para no congelar este proceso si el SIAT no responde.
            $this->isOnline = $service->verificarOnlineSiat();

            // 2. Verificar si hay un evento de contingencia activo en tu BD local
            // (Aquí debes adaptar según tu modelo de eventos, esto es un ejemplo)
            // $activeEvent = $this->branch->siatEvents()->where('status', 'active')->first();
            
            // SIMULACIÓN DE DATOS (Reemplazar con tu lógica real de DB)
            $activeEvent = $serviceEvent->getEventActive($this->branch->amyrConnectionBranch->point_sale); // Cambiar a la consulta real
            
            if ($activeEvent) {
                $this->hasActiveContingency = true;
                $this->eventId = $activeEvent['id']; // o codigo_recepcion
                $this->eventStartTime = $activeEvent['fecha_inicio']; // format('H:i d/m')
                $this->eventDescription = $activeEvent['descripcion'] ?? '';
            } else {
                $this->hasActiveContingency = false;
                $this->eventId = null;
            }

        } catch (\Exception $e) {
            $this->isOnline = false;
            Log::error("Error verificando estado SIAT: " . $e->getMessage());
        }
    }

    public function openContingencyModal()
    {
        $this->showModal = true;
    }

    public function createContingencyEvent()
    {
        // Validación básica
        if(empty($this->reason)) {
            Notification::make()->title('Error')->body('Debe seleccionar un motivo.')->danger()->send();
            return;
        }

        // --- LÓGICA PARA CREAR EL EVENTO EN TU BASE DE DATOS Y/O SIAT ---
        // $service = new MonoInvoiceApiService($this->branch);
        // $service->iniciarContingencia(...)
        
        // Simulación de éxito
        $this->hasActiveContingency = true;
        $this->eventId = rand(1000, 9999);
        $this->eventStartTime = now()->format('H:i d/m');
        
        Notification::make()->title('Contingencia Iniciada')->success()->send();
        $this->showModal = false;
    }

    public function closeContingencyEvent()
    {
        // --- LÓGICA PARA CERRAR EL EVENTO EN TU BASE DE DATOS Y/O SIAT ---
        $service = new AmyrEventsApiService($this->branch->amyrConnectionBranch->token);
        try{
            $response = $service->closeEvent($this->eventId);
            if(strcmp($response['response'], 'ok') == 0){
                $this->hasActiveContingency = false;
                $this->eventId = null;
                $this->eventStartTime = null;

                Notification::make()->title('Contingencia Cerrada')
                    ->body($response['message'])
                    ->success()->send();
            } else {
                Notification::make()->title('Error Cerrando Contingencia')
                    ->body('No se pudo cerrar el evento de contingencia: ' . $response['message'])->danger()->send();
            }        // Simulación de cierre
        } catch(\Exception $e){
            Notification::make()->title('Error Cerrando Contingencia')
                ->body($e->getMessage())
                ->danger()->send();
        }
        
    }   

    public function createEvent($eventKey)
    {
        $fechaInicio = now();
        // dd("sss:", [
        //     "fecha actual" => $fechaInicio->format('Y-m-d H:i:s'),
        //     "fecha menos 2 minutos" => $fechaInicio->subMinutes(2)->format('Y-m-d H:i:s'),
        // ]);
        $eventSiatDto = new EventSiatDto([
            'sucursal_id' => $this->branch->amyrConnectionBranch->branch_siat_id,
            'puntoventa_id' => $this->branch->amyrConnectionBranch->point_sale,
            'evento_id' => $eventKey,
            'fecha_inicio' => $fechaInicio->subMinutes(2)->format('Y-m-d H:i:s'),
        ]);

        $service = new AmyrEventsApiService($this->branch->amyrConnectionBranch->token);
        try {
            $response = $service->createEvent($eventSiatDto);
            if ($response) {
                Notification::make()->title('Evento Creado')
                    ->body('Evento SIAT creado con ID: ' . $response['id'])
                    ->success()->send();
            } else {
                Notification::make()->title('Error Creando Evento')
                    ->body('No se pudo crear el evento SIAT.')->danger()->send();
            }
        } catch (\Exception $e) {
            Notification::make()->title('Error Creando Evento')
                ->body($e->getMessage())
                ->danger()->send(); 
        }
    }

    public function render()
    {
        return view('livewire.branch.siat-connection-status');
    }
}
