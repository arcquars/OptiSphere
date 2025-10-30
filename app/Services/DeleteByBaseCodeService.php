<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class DeleteByBaseCodeService
{
    public function delete(string $baseCode): bool
    {
        return DB::transaction(function () use ($baseCode) {
            $productsToDelete = Product::whereHas('opticalProperties', function($query) use ($baseCode){
                $query->where('base_code', 'like', $baseCode);
            })->get();

            if ($productsToDelete->isEmpty()) {
                throw new \Exception("No existen productos a borrar con Código base: " . $baseCode);
            }

            foreach ($productsToDelete as $product) {
                $product->opticalProperties()->delete();
                $product->delete();
            }

            return true;
        });
    }
}
