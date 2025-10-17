<?php

namespace App\Livewire\Sale;

use App\Models\Sale;
use App\Services\SaleService;
use Filament\Notifications\Notification;
use Livewire\Component;

class DeleteSaleModal extends Component
{
    public bool $showForm = false;
    public Sale $sale;

    public int $voidSaleId;
    public string $voidReason = '';

    protected $listeners = ['toggleDeleteSale' => 'confirmVoid'];

    public function confirmVoid($saleId= null): void
    {
        if($saleId){
            $this->sale = Sale::find($saleId);
        }
        $this->showForm = !$this->showForm;
    }

    public function closeModal(){
        $this->showForm = false;
    }

    public function voidSale(SaleService $service)
    {
        try {
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
