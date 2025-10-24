<?php

namespace App\Livewire\Branch;

use App\Models\Branch;
use App\Models\CashBoxClosing;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CashOpenModal extends Component
{
    public bool $showFormCashOpen = false;
    public $branchList = [];

    protected $listeners = ['toggleViewCashOpen' => 'toggleCashOpenForm'];

    protected function rules(): array
    {
        return [
            'branchList.*.initial_balance' => ['nullable', 'numeric', 'min:0', 'max:10000'],
        ];
    }

    public function toggleCashOpenForm(): void
    {
        $this->showFormCashOpen = !$this->showFormCashOpen;
        if($this->showFormCashOpen){
            $this->loadBranchArray();
        }
    }

    public function loadBranchArray(){
        $branches = new Collection;
        $userId = Auth::id();
        if(Auth::user()->hasRole('admin')){
            $branches = Branch::where('is_active', true)->get();
        } else {
            $branches = User::find($userId)->branches;
        }
        foreach ($branches as $bTemp){
            $open = $bTemp->isOpenCashBoxClosingByUser($userId);
            $this->branchList['branch-'.$bTemp->id] = [
                'id' => $bTemp->id,
                'name' => $bTemp->name,
                'initial_balance' => null,
                'opening_time' => ($open)? $bTemp->getCashBoxClosingByUser($userId)->opening_time : '',
                'open' => $open
            ];
        }
    }

    public function openBranch($id){
        $this->validate();
        if($this->branchList['branch-'.$id]['initial_balance'] != null && $this->branchList['branch-'.$id]['initial_balance'] != ""){
            $user = Auth::user();

            $data = [
                'branch_id' => $id,
                'initial_balance' => $this->branchList['branch-'.$id]['initial_balance'],

            ];
            // 1. Verificar si ya hay una caja abierta (una doble verificación)
            $openBox = CashBoxClosing::where('user_id', $user->id)
                ->where('branch_id', $user->branch_id)
                ->where('status', CashBoxClosing::STATUS_OPEN)
                ->first();

            if ($openBox) {
                Notification::make()
                    ->title('Error de Apertura')
                    ->body('Ya tienes una caja abierta. Debes cerrarla antes de abrir una nueva.')
                    ->danger()
                    ->send();
                return;
            }

            // 2. Crear el registro de Apertura
            DB::transaction(function () use ($user, $data) {
                CashBoxClosing::create([
                    'branch_id' => $data['branch_id'],
                    'user_id' => $user->id,
                    'opening_time' => now(),
                    'initial_balance' => $data['initial_balance'],
                    'expected_balance' => $data['initial_balance'], // Inicialmente es solo el fondo
                    'actual_balance' => 0, // 0 al abrir
                    'difference' => 0,
                    'status' => CashBoxClosing::STATUS_OPEN,
                ]);
            });

            // 3. Notificación de éxito
            Notification::make()
                ->title('Caja Abierta')
                ->body('Caja abierta exitosamente con fondo de Bs. ' . number_format($data['initial_balance'], 2) . '.')
                ->success()
                ->send();
            $this->loadBranchArray();
        }
        $this->addError('branchList.branch-'.$id.'.initial_balance', 'El monto ingresado no es válido.');
    }

    public function saveOpenCash(){
        dd("xxxx");
    }
    public function render()
    {
        return view('livewire.branch.cash-open-modal');
    }
}
