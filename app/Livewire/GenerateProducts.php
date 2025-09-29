<?php

namespace App\Livewire;

use App\Helpers\GenerateProductHelper;
use App\Http\Requests\StoreGenerateProductsRequest;
use App\Models\Product;
use App\Models\Price;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class GenerateProducts extends Component
{
    public $suppliers;

    public $baseCode, $supplier;

    public $priceNormal, $priceSpecial, $priceWholesale;

    public function mount(){
        $this->suppliers = Supplier::all();
    }

    public function rules(){
        return (new StoreGenerateProductsRequest())->rules();
    }

    public function render()
    {
        return view('livewire.generate-products');
    }

    public function generateProducts(){
        $validateData = $this->validate();

        $productsToCreate = GenerateProductHelper::generateZeroToSix(strtoupper($this->baseCode), $this->supplier);
        $price1 = [
            "type" => Price::TYPE_NORMAL,
            "price" => $this->priceNormal
        ];
        $price2 = [
            "type" => Price::TYPE_ESPECIAL,
            "price" => $this->priceSpecial ?: $this->priceNormal
        ];
        $price3 = [
            "type" => Price::TYPE_MAYORISTA,
            "price" => $this->priceWholesale ?: $this->priceNormal
        ];
        try{
            DB::transaction(function () use ($productsToCreate, $price1, $price2, $price3) {
                foreach ($productsToCreate as $productData){
                    $opticalPropertiesData = $productData['opticalProperties'];
                    unset($productData['opticalProperties']);

                    // 1. Crea el producto principal
                    $product = Product::create($productData);

                    // 2. Usa la relación para crear las propiedades ópticas
                    $product->opticalProperties()->create($opticalPropertiesData);
                    $product->prices()->create($price1);
                    $product->prices()->create($price2);
                    $product->prices()->create($price3);
                }
            });
        } catch (\Exception $e){
            dd($e->getMessage());
        }

        return $this->redirect(route('filament.admin.resources.products.index'), navigate: true);
    }
}
