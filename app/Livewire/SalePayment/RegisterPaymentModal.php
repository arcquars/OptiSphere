<?php

namespace App\Livewire\SalePayment;

use App\Models\Sale;
use App\Models\SalePayment;
use App\Services\CreditService;
use Filament\Notifications\Notification;
use FontLib\TrueType\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class RegisterPaymentModal extends Component
{
    use WithPagination;

    public bool $showForm = false;
    public ?Sale $sale;

    public $amountPayment;
    public $paymentType = SalePayment::METHOD_CASH;
    public $notes;
    protected $listeners = ['toggleViewSalePayment' => 'toggleForm'];

    protected function rules()
    {
        return [
            'amountPayment' => 'required|numeric|min:0.1|max:'.($this->sale->final_total-$this->sale->total_partial_payments),
        ];
    }


    public function toggleForm($saleId= null): void
    {
        if($saleId){
            $this->resetValidation();

            $this->sale = Sale::find($saleId);
        }
        $this->showForm = !$this->showForm;
    }

    public function render()
    {
        $payments = new Collection;
        if(isset($this->sale))
            $payments = $this->sale->payments()->paginate(3);
        return view('livewire.sale-payment.register-payment-modal', compact('payments'));
    }

    public function registerPayment(CreditService $svc){
        $this->validate();
        $svc->registerPayment(
            $this->amountPayment,
            $this->sale,
            $this->paymentType,
            Auth::id(),
            $this->notes
        );
        $this->toggleForm();
        $this->paymentType = SalePayment::METHOD_CASH;
        $this->amountPayment = null;
        $this->notes = null;
        $this->sale = null;
        Notification::make()
            ->title('Registro pago parcial')
            ->body('Se registro un pago parcial a una venta a credito')
            ->success()
            ->send();
    }
}
