<?php

return [
    'company_name' => 'Cerisier S.R.L.',
    'sphere_step' => 0.25,
    'cylinder_step' => 0.25,
    'sphere_range' => [0, 6],
    'cylinder_range' => [0, 6],
    'tipo_documento_identidad' => [
        1 => 'CI - CÉDULA DE IDENTIDAD',
        2 => 'CEX - CÉDULA DE IDENTIDAD DE EXTRANJERO',
        3 => 'NIT - NÚMERO DE IDENTIFICACIÓN TRIBUTARIA',
        4 => 'PAS - PASAPORTE',
        5 => 'OD - OTRO DOCUMENTO DE IDENTIDAD'
    ],
//    'tipo_cliente' => ['normal' => 'Normal', 'especial' => 'Especial', 'mayorista' => 'Mayorista']
    'tipo_cliente' => ['normal' => 'Normal', 'mayorista' => 'Mayorista'],
    'currency_symbol' => "BOB",
    'pagination' => 5
];
