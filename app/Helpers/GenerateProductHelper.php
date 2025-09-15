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
                    $code = $baseCode . $type . GenerateProductHelper::formatNumberCode($sphere) . "-" . GenerateProductHelper::formatNumberCode($cylinder);
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

    public static function formatNumberCode($num){
        $string = (string) $num;
        $cadenaSinPunto = str_replace('.', '', $string);

        if (strlen($cadenaSinPunto) < 3) {
            $cadenaSinPunto = str_pad($cadenaSinPunto, 3, '0', STR_PAD_RIGHT);
        }

        return $cadenaSinPunto;
    }
}
