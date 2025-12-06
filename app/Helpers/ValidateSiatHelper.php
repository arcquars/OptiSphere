<?php

namespace App\Helpers;

use App\Models\Product;
use App\Models\Service;

class ValidateSiatHelper
{
    public static function isValidSiatCode($items): bool
    {
        $valid = false;
        foreach ($items as $item) {
            if($item['type'] == 'product'){
                $product = Product::find($item['id']);
                if(
                    $product && 
                    $product->siat_branch_id && 
                    $product->siat_data_medida_code && 
                    $product->siat_data_actividad_code && 
                    $product->siat_data_product_code){
                        $valid = true;
                    }
                    else {
                        return false;
                    }
            } else {
                $service = Service::find($item['id']);
                if(
                    $service && 
                    $service->siat_branch_id && 
                    $service->siat_data_medida_code && 
                    $service->siat_data_actividad_code && 
                    $service->siat_data_product_code){
                        $valid = true;
                    } else {
                        return false;
                    }
            }
        }
        return $valid;
    }
}