<?php

namespace App\Livewire\Sale;

use App\Models\Sale;
use App\Services\AmyrCatalogsService;
use App\Services\MonoInvoiceApiService;
use App\Services\SaleService;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class DeleteSaleModal extends Component
{
    public bool $showForm = false;
    public Sale $sale;

    public int $voidSaleId;

    public bool $isSiat = false;
    public string $voidReason = '';

    public array $motivosAnulacion =  [];

    public $motivo;
    protected $listeners = ['toggleDeleteSale' => 'confirmVoid'];

    /**
     * @param AmyrCatalogsService $amyrCatalogsService
     */
    public function confirmVoid(AmyrCatalogsService $amyrCatalogsService, $saleId= null): void
    {
        if($saleId){
            $this->sale = Sale::find($saleId);
            $this->motivo = null;
            if(isset($this->sale->siat_invoice_id) && isset($this->sale->siat_status) && strcmp($this->sale->siat_status, "issued") == 0){
                $amyrCatalogsService->setToken($this->sale->branch->amyrConnectionBranch->token);
                $result = $amyrCatalogsService->getMotivoAnulacion();
                // dd($result['RespuestaListaParametricas']['transaccion']);
                if($result['RespuestaListaParametricas']['transaccion'] && count($result['RespuestaListaParametricas']['listaCodigos']) > 0){
                    $this->isSiat = true;
                    $this->motivosAnulacion = $result['RespuestaListaParametricas']['listaCodigos'];
                } else {
                    $this->isSiat = false;
                    $this->motivosAnulacion = [];
                }
            } else {
                $this->isSiat = false;
            }
        }
        $this->showForm = !$this->showForm;
    }

    public function closeModal(){
        $this->showForm = false;
    }

    public function voidSale(SaleService $service)
    {
        if($this->isSiat){
            $validated = Validator::make(
                // Data to validate...
                ['motivo' => $this->motivo],
    
                // Validation rules to apply...
                ['motivo' => 'required'],
    
                // Custom validation messages...
                ['required' => 'El :attribute es requerido'],
            )->validate();
        }
        try {
            if($this->isSiat){
                $monoInvoiceApiService = new MonoInvoiceApiService($this->sale->branch);
                $result = $monoInvoiceApiService->voidInvoice($this->sale->siat_invoice_id, $this->motivo);
                if(strcmp($result['response'], 'ok') == 0 && $result['code'] === 200){
                    $this->sale->siat_status = 'void';
                    $this->sale->save();
                } else {
                    Notification::make()
                        ->title('Error Siat')
                        ->body("Error no detectado en SIAT")
                        ->danger()
                        ->send();        
                }
                // dd($result);
            }

            // dd("ddd");
            $service->voidSale(
                saleId: $this->sale->id,
                userId: auth()->id(),
                reason: $this->voidReason ?: null
            );

            $this->closeModal();
            Notification::make()
                ->title('Exito')
                ->body("¡Venta N° {$this->sale->id} anulada y stock restaurado.")
                ->success()
                ->send();
            $this->reset('voidSaleId', 'voidReason');

            // Refresca listas/carrito/estadísticas
            $this->dispatch('refresh-report-sales');
        } catch (\DomainException $e) {
            Notification::make()
                ->title('Error DomainException')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } catch (\Throwable $e) {
            report($e);
            Notification::make()
                ->title('Error Throwable')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.sale.delete-sale-modal');
    }
}
