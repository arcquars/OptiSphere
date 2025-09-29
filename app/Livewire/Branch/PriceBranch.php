<?php

namespace App\Livewire\Branch;

use App\Models\Branch;
use App\Models\Price;
use App\Models\Product;
use App\Models\ProductBranchPrices;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;
use Livewire\Component;

class PriceBranch extends Component
{
    public $branch;

    public Product $product;
    public bool $showForm = false;

    public $message = "";

    #[Validate('required|numeric|min:0|max:2000000')]
    public $priceNormal, $priceEspecial, $priceMayor;

    protected $listeners = ['togglePriceProductForm' => 'toggleForm'];

    public function mount($branchId): void
    {
        $this->branch = Branch::find($branchId);
    }

    public function toggleForm($productId= null): void
    {
        // Resetea las propiedades públicas a su estado inicial
        $this->reset(['priceNormal', 'priceEspecial', 'priceMayor']);

        // Limpia todos los errores de validación
        $this->resetValidation();
//        $this->priceNormal = null;
//        $this->priceEspecial = null;
//        $this->priceMayor = null;

        if($productId) {
            $this->product = Product::find($productId);
            $priceNormal = $this->product->getPriceForBranch($this->branch->id, ProductBranchPrices::TYPE_NORMAL);
            $priceEspecial = $this->product->getPriceForBranch($this->branch->id, ProductBranchPrices::TYPE_ESPECIAL);
            $priceMAyor = $this->product->getPriceForBranch($this->branch->id, ProductBranchPrices::TYPE_MAYORISTA);

            if($priceNormal)
                $this->priceNormal = $priceNormal->price;
            if($priceEspecial)
                $this->priceEspecial = $priceEspecial->price;
            if($priceMAyor)
                $this->priceMayor = $priceMAyor->price;
        }

        $this->showForm = !$this->showForm;
//        if($this->showForm){
//        }
    }

    public function savePrices(){
        $validated = $this->validate();
        DB::beginTransaction();

        try {
            $this->product->prices()->updateOrCreate(
            // 1. Atributos para buscar (la clave única del precio para este producto)
                [
                    'type' => Price::TYPE_NORMAL,
                    'branch_id' => $this->branch->id,
                ],
                // 2. Valores para actualizar o crear
                [
                    'price' => $this->priceNormal,
                    'user_id' => Auth::id(),
                ]
            );

            $this->product->prices()->updateOrCreate(
                [
                    'type' => Price::TYPE_ESPECIAL,
                    'branch_id' => $this->branch->id,
                ],
                [
                    'price' => $this->priceEspecial,
                    'user_id' => Auth::id(),
                ]
            );

            $this->product->prices()->updateOrCreate(
                [
                    'type' => Price::TYPE_MAYORISTA,
                    'branch_id' => $this->branch->id,
                ],
                [
                    'price' => $this->priceMayor,
                    'user_id' => Auth::id(),
                ]
            );

            Notification::make()
                ->title('Éxito')
                ->body('Se actualizaron los precios para este producto')
                ->success()
                ->send();
            DB::commit();
        } catch (Throwable $e) {
            // En caso de error, deshacer todo
            DB::rollback();

            Notification::make()
                ->title('Error')
                ->body('Ocurrio un error al momento de guardar los cambios.')
                ->success()
                ->send();
        }
        $this->showForm = false;
    }

    public function render()
    {
        return view('livewire.branch.price-branch');
    }
}
