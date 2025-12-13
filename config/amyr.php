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
];