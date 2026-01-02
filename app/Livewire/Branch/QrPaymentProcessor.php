<?php

namespace App\Livewire\Branch;

use App\Models\PagoQr;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class QrPaymentProcessor extends Component
{
    public $qrId;

    public $shouldPoll = true;
    // Solo este pequeño componente se refrescará cada 5-10 segundos
    public function checkStatus12() {
        Log::info("ssss: " . $this->qrId);
        $pagoQr = PagoQr::where('qr_id', "=", $this->qrId)->where('status', '=', 1)->first();
        if ($pagoQr) {
            $this->shouldPoll = false;
            $this->dispatch('payment-confirmed'); // Avisar al padre
        } else {
            $this->shouldPoll = true;
        }
    }

    public function render()
    {
        return view('livewire.branch.qr-payment-processor');
    }
}
