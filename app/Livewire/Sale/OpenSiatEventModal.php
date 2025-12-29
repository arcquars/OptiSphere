<?php

namespace App\Livewire\Sale;

use App\DTOs\EventSiatDto;
use App\Models\Branch;
use App\Services\AmyrEventsApiService;
use App\Services\MonoInvoiceApiService;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class OpenSiatEventModal extends Component
{
    public bool $showForm = false;

    public Branch $branch;
    public ?string $codeEvent;
    public string $nameEvent;
    public array $cufds = [];
    public $invoiceApiService;

    public $cufd;
    public $dateIni;
    public $dateEnd;

    protected $listeners = ['toggleSiatEvent' => 'toggleSiat'];

    public function mount($branchId)
    {
        $this->branch = Branch::find($branchId);
        $invoiceApiService = new MonoInvoiceApiService($this->branch);
        $this->buildCufds($invoiceApiService->getCufds());
    }

    protected function rules()
    {
        return [
            'cufd' => 'required',
            'dateIni' => 'required|date',
            'dateEnd' => 'required|date|after_or_equal:dateIni',
        ];
    }

    public function toggleSiat($codeEvent = null): void
    {
        if ($codeEvent) {
            $this->codeEvent = $codeEvent;
            $this->nameEvent = config('amyr.eventos_siat_cafc', [])[$codeEvent] ?? "";
        } else {
            $this->codeEvent = null;
            $this->nameEvent = "";
        }
        $this->showForm = !$this->showForm;
    }

    public function closeModal()
    {
        $this->cufd = null;
        $this->dateIni = null;
        $this->dateEnd = null;
        $this->showForm = false;
    }

    private function buildCufds($cufds)
    {
        $this->cufds = [];
        foreach ($cufds['data'] as $cufd) {
            $this->cufds[] = ["codigo" => $cufd["codigo"], "name" => $cufd["creation_date"] . " - " . $cufd["fecha_vigencia"]];
        }
    }

    public function saveEvent()
    {
        $this->validate();
        try {
            // 2. Instanciar el servicio con el token de la sucursal
            $token = $this->branch->amyrConnectionBranch->token;
            $eventsService = new AmyrEventsApiService($token);

            // 3. Preparar el DTO con los datos para la API
            // Nota: Asegúrate de que EventSiatDto tenga estos campos en su constructor/método fromArray
            $eventDto = new EventSiatDto([
                'cufd_evento' => $this->cufd, // El ID del CUFD seleccionado
                'fecha_inicio' => $this->dateIni,
                'fecha_fin' => $this->dateEnd,
                'evento_id' => $this->codeEvent,
                'descripcion' => $this->nameEvent,
                'sucursal_id' => $this->branch->amyrConnectionBranch->sucursal,
                'puntoventa_id' => $this->branch->amyrConnectionBranch->point_sale,
            ]);

            // 4. Llamar al servicio para crear el evento
            $response = $eventsService->createEvent($eventDto);

            if ($response) {
                // Éxito
                Notification::make()
                    ->title('Evento creado')
                    ->body("El evento de contingencia se registró correctamente. Código: " . ($response['codigoRecepcionEventoSignificativo'] ?? 'N/A'))
                    ->success()
                    ->send();

                $this->closeModal();
                
                $this->dispatch('set-event-active');
                $this->dispatch('siat-checkstatus');

            } else {
                // Fallo lógico (la API respondió pero no con éxito)
                Notification::make()
                    ->title('Error')
                    ->body('No se pudo registrar el evento en el servicio SIAT.')
                    ->danger()
                    ->send();
            }

        } catch (\Exception $e) {
            // Error técnico o excepción del servicio
            Log::error("Error al guardar evento SIAT: " . $e->getMessage());
            
            Notification::make()
                ->title('Error')
                ->body('Ocurrió un error al procesar la solicitud: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.sale.open-siat-event-modal');
    }


}
