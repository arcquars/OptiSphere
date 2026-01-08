<?php

namespace App\Livewire\Sale;

use App\Models\Sale;
use App\Services\AmyrCatalogsService;
use Livewire\Component;
use App\Services\SaleService;
use Illuminate\Support\Facades\Validator;
use Filament\Notifications\Notification;
use App\Services\MonoInvoiceApiService;

class RevertSaleModal extends Component
{
    public bool $showForm = false;
    public Sale $sale;

    public int $revertSaleId;

    public bool $isSiat = false;

    protected $listeners = ['toggleRevertirAnularSale' => 'confirmRevert'];
    
    /**
     * @param AmyrCatalogsService $amyrCatalogsService
     */
    public function confirmRevert(AmyrCatalogsService $amyrCatalogsService, $saleId= null): void
    {
        if($saleId){
            $this->sale = Sale::find($saleId);
            if(isset($this->sale->siat_invoice_id) && isset($this->sale->siat_status) && strcmp($this->sale->siat_status, "issued") == 0){
                $amyrCatalogsService->setToken($this->sale->branch->amyrConnectionBranch->token);
                $result = $amyrCatalogsService->getMotivoAnulacion();
                // dd($result['RespuestaListaParametricas']['transaccion']);
                if($result['RespuestaListaParametricas']['transaccion'] && count($result['RespuestaListaParametricas']['listaCodigos']) > 0){
                    $this->isSiat = true;
                } else {
                    $this->isSiat = false;
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

    public function revertSale()
    {
        try {
            $monoInvoiceApiService = new MonoInvoiceApiService($this->sale->branch);
            $result = $monoInvoiceApiService->revertInvoice($this->sale->siat_invoice_id);
            if(strcmp($result['response'], 'ok') == 0 && $result['code'] === 200){
                $this->sale->siat_status = 'revert';
                $this->sale->save();
                Notification::make()
                ->title('Exito')
                ->body("Anulación revertida con éxito.")
                ->success()
                ->send();
            } else {
                Notification::make()
                    ->title('Error Siat')
                    ->body("Error no detectado en SIAT")
                    ->danger()
                    ->send();        
            }
            $this->closeModal();
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
        return view('livewire.sale.revert-sale-modal');
    }
}
