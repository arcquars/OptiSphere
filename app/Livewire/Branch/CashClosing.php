<?php

namespace App\Livewire\Branch;

use App\Models\Branch;
use App\Models\CashBoxClosing;
use App\Services\CashClosingService;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class CashClosing extends Component
{
    public ?int $branchId = null;
    public ?int $userId = null;

    public ?int $closingId = null;
    public ?string $from = null;   // fecha/hora inicio (por defecto opened_at)
    public ?string $until = null;  // fecha/hora fin (por defecto now())
    public ?string $notes = null;

//    public float $closingAmount = 0.0; // contado por el cajero
    public ?float $closingAmount = null; // contado por el cajero

    public ?CashBoxClosing $cashBoxClosing = null;

    public function mount(CashClosingService $svc): void
    {
        $user = auth()->user();

        abort_unless($svc->userCanClose($user), 403);
//        $this->svc = $svc;

    }

    #[On('load-by-branch')]
    public function loadByBranch(CashClosingService $svc, ?int $branchId = null){
        if($branchId == null){
            $this->branchId = null;
            return;
        }
        $this->userId   = Auth::id();
        $this->branchId = $branchId ?: ($user->branch_id ?? null); // ajusta si usas many-to-many

        $this->cashBoxClosing = Branch::find($branchId)->getCashBoxClosingByUser($this->userId);
//        $closing = $svc->getOpenClosingForUser($this->userId, $this->branchId, createIfMissing: true);
        $this->closingId = $this->cashBoxClosing->id;
        $this->from = $this->cashBoxClosing?->opening_time?->format('Y-m-d H:i');
        $this->until = now()->format('Y-m-d H:i');
    }

    #[Computed]
    public function closing(): ?CashBoxClosing
    {
        return $this->closingId ? CashBoxClosing::find($this->closingId) : null;
    }

    #[Computed]
    public function totals(): array
    {
        if (! $this->closing) return [
            'sales' => ['cash'=>0,'transfer'=>0,'qr'=>0],
            'credit_payments' => ['cash'=>0,'transfer'=>0,'qr'=>0],
            'movements' => ['incomes'=>0,'expenses'=>0],
            'system_total' => 0,
        ];

        $svc = app(CashClosingService::class);

        // Si el usuario es admin, puede ver por usuario; si no, se fija a sí mismo
        $userFilter = auth()->user()->hasRole('admin') ? ($this->userId) : auth()->id();

        return $svc->computeTotals(
            closing: $this->closing,
            from: $this->from,
            until: $this->until,
            userIdFilter: $userFilter,
        );
    }

    public function refreshTotals(): void
    {
        // Solo para forzar recomputado
        $this->until = now()->format('Y-m-d H:i');
        $this->closingAmount = null;
    }

    public function close(CashClosingService $svc): void
    {
        $this->validate([
            'closingAmount' => ['required','numeric','min:0'],
            'from'  => ['nullable','date'],
            'until' => ['nullable','date','after_or_equal:from'],
        ]);

        $userFilter = auth()->user()->hasRole('admin') ? ($this->userId) : auth()->id();

        $closing = $svc->close(
            closing: $this->closing,
            closingAmount: (float) $this->closingAmount,
            notes: $this->notes,
            from: $this->from,
            until: $this->until,
            userIdFilter: $userFilter,
        );

        $this->branchId = null;

        Notification::make()
            ->title('Cerrar Caja')
            ->body("Caja cerrada correctamente.")
            ->success()
            ->send();
    }

    public function render()
    {
        return view('livewire.branch.cash-closing');
    }
}
