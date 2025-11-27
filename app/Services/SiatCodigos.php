<?php

namespace App\Services;

use Amyrit\SiatBoliviaClient\Data\Requests\SolicitudCufd;
use Amyrit\SiatBoliviaClient\Data\Requests\SolicitudCuis;
use Amyrit\SiatBoliviaClient\Data\Requests\SolicitudRegistroPuntoVenta;
use Amyrit\SiatBoliviaClient\Data\Responses\RespuestaCufd;
use Amyrit\SiatBoliviaClient\SiatClient;
use Amyrit\SiatBoliviaClient\SiatConfig;
use App\Models\SiatProperty;

class SiatCodigos
{
    public function getCufd(SiatProperty $siatProperty): RespuestaCufd
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

        $solicitudRPV = new SolicitudCufd(
            codigoPuntoVenta: $siatProperty->siatSucursalPuntoVentaActive->punto_venta,
            codigoSucursal: $siatProperty->siatSucursalPuntoVentaActive->sucursal
        );

        

        $respuestaPuntoVenta = $client->codigos()->cufd($solicitudRPV);

        return $respuestaPuntoVenta;
    }
}