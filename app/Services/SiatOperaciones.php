<?php

namespace App\Services;

use Amyrit\SiatBoliviaClient\Data\Requests\SolicitudCufd;
use Amyrit\SiatBoliviaClient\Data\Requests\SolicitudCuis;
use Amyrit\SiatBoliviaClient\Data\Requests\SolicitudRegistroPuntoVenta;
use Amyrit\SiatBoliviaClient\SiatClient;
use Amyrit\SiatBoliviaClient\SiatConfig;
use App\Models\SiatProperty;

class SiatOperaciones
{
    

    public function registrarPuntoVenta(SiatProperty $siatProperty)
    {
        $config = new SiatConfig([
            'codigoSistema' => $siatProperty->system_code,
            'nit' => $siatProperty->nit,
            'apiKey' => $siatProperty->token,
            'cuis' => $siatProperty->siatSucursalPuntoVentaActive->cuis,
            'ambiente' => $siatProperty->environment,
            'modalidad' => $siatProperty->modality,
            'codigoSucursal' => $siatProperty->siatSucursalPuntoVentaActive->sucursal,
            'codigoPuntoVenta' => $siatProperty->siatSucursalPuntoVentaActive->punto_venta,
        ]);

        $client = new SiatClient($config);

        $solicitudRPV = new SolicitudRegistroPuntoVenta(
            codigoSucursal: $siatProperty->siatSucursalPuntoVentaActive->sucursal,
            nombrePuntoVenta: $siatProperty->siatSucursalPuntoVentaActive->punto_venta . ".- " . $siatProperty->branch->name,
            descripcion: 'PUNTO DE VENTA CAJEROS: '.$siatProperty->branch->name,
            codigoTipoPuntoVenta: 5
            
        );

        

        $respuestaPuntoVenta = $client->operaciones()->registroPuntoVenta($solicitudRPV);

        // // Asignamos el CUIS obtenido a nuestra configuración
        // Log::info("¡ÉXITO! CUIS obtenido: ");
        // Log::info(json_encode($respuestaCuis));

        return $respuestaPuntoVenta;
    }

    
}