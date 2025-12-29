<?php

// app('url') es una función auxiliar de Laravel que te permite acceder a la URL principal de la aplicación.
// En este caso, construimos la URL base a partir de las variables de entorno definidas.

return [
    'base_url' => env('AMYR_APIREST_HOST', 'http://127.0.0.1') . 
                  (env('AMYR_APIREST_PORT', 80) ? ':' . env('AMYR_APIREST_PORT') : '') . 
                  env('AMYR_APIREST_HOST_SUBDIR', '') . 
                  '/' . 
                  env('AMYR_APIREST_BASEURL', 'api'),

    // Variables de entorno usadas para la configuración de la URL
    'env_config' => [
        'host' => env('AMYR_APIREST_HOST', 'http://127.0.0.1'),
        'port' => env('AMYR_APIREST_PORT', 80),
        'subdir' => env('AMYR_APIREST_HOST_SUBDIR', ''),
        'api_base' => env('AMYR_APIREST_BASEURL', 'api'),
    ],

    'document_types' => [
        '5' => 'NIT',
        '1' => 'CI',
        '2' => 'CEX',
        '3' => 'PAS',
        '4' => 'OD'
    ],

    'tipo_documento_identidad' => [
        '1' => 'CI - CÉDULA DE IDENTIDAD',
        '2' => 'CEX - CÉDULA DE IDENTIDAD DE EXTRANJERO',
        '5' => 'NIT - NÚMERO DE IDENTIFICACIÓN TRIBUTARIA',
        '3' => 'PAS - PASAPORTE',
        '4' => 'OD - OTRO DOCUMENTO DE IDENTIDAD'
    ],

    'eventos_siat' => [
        '1' => 'CORTE DEL SERVICIO DE INTERNET',
        '2' => 'INACCESIBILIDAD AL SERVICIO WEB DE LA ADMINISTRACIÓN TRIBUTARIA',
        '3' => 'INGRESO A ZONAS SIN INTERNET POR DESPLIEGUE DE PUNTO DE VENTA',
        '4' => 'VENTA EN LUGARES SIN INTERNET'
    ],
    'eventos_siat_cafc' => [
        '5' => 'VIRUS INFORMÁTICO O FALLA DE SOFTWARE',
        '6' => 'CAMBIO DE INFRAESTRUCTURA DE SISTEMA O FALLA DE HARDWARE',
        '7' => 'CORTE DE SUMINISTRO DE ENERGIA ELÉCTRICA',
    ],
];