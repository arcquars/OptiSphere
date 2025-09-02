<?php

namespace App\Helpers;

class GenerateProductHelper
{
    public static function generateZeroToSix($baseCode, $supplier): array
    {
        $products = [];
        $sphereStep = config('cerisier.sphere_step');
        $cylinderStep = config('cerisier.cylinder_step');
        $sphereRange = config('cerisier.sphere_range');
        $cylinderRange = config('cerisier.cylinder_range');

        $types = ['+', '-'];

        foreach ($types as $type){
            $cylinder = $cylinderRange[0];
            while ($cylinder <= $cylinderRange[1]){
                $sphere = $sphereRange[0];
                while($sphere <= $sphereRange[1]){
                    $code = $baseCode . $type . $sphere . "-" . $cylinder;
                    $products[] = [
                        "name" => $code,
                        "code" => $code,
                        "supplier_id" => $supplier,
                        "opticalProperties" => [
                            "base_code" => $baseCode,
                            "type" => $type,
                            "sphere" => $sphere,
                            "cylinder" => $cylinder
                        ],
                    ];
                    $sphere += $sphereStep;
                }
                $cylinder += $cylinderStep;
            }
        }

        return $products;
    }
}
