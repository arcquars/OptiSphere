<?php

namespace App\Services;

use Amyrit\SiatBoliviaClient\Data\Requests\SolicitudSincronizacion;
use Amyrit\SiatBoliviaClient\Data\Responses\RespuestaCuis;
use Amyrit\SiatBoliviaClient\Exceptions\SiatException;
use Amyrit\SiatBoliviaClient\SiatClient;
use Amyrit\SiatBoliviaClient\SiatConfig;
use App\Models\SiatProperty;
use \Amyrit\SiatBoliviaClient\Data\Requests\SolicitudCuis;
use Exception;
use Log;

class SiatService
{

    public function validConfig(SiatProperty $siatProperty): bool
    {
        $result = false;
        $config = new SiatConfig([
            'codigoSistema' => $siatProperty->system_code,
            'nit' => $siatProperty->nit,
            'apiKey' => $siatProperty->token,
            'ambiente' => $siatProperty->environment,
            'modalidad' => $siatProperty->modality,
            'codigoSucursal' => $siatProperty->siatSucursalPuntoVentaActive->sucursal,
            'codigoPuntoVenta' => $siatProperty->siatSucursalPuntoVentaActive->punto_venta,
        ]);

        $client = new SiatClient($config);
        $solicitudCuis = new SolicitudCuis(
            codigoPuntoVenta: $siatProperty->siatSucursalPuntoVentaActive->punto_venta,
            codigoSucursal: $siatProperty->siatSucursalPuntoVentaActive->sucursal
        );

        $respuestaCuis = $client->codigos()->cuis($solicitudCuis);

        if($respuestaCuis->transaccion === true ){
            // Asignamos el CUIS obtenido a nuestra configuración
            $result = true;    
        }
        
        return $result;
    }

    public function getCuis(SiatProperty $siatProperty, $codigoSucursal = null, $codigoPuntoVenta = null)
    {
        if($codigoSucursal === null || $codigoPuntoVenta === null){
            $codigoSucursal = $siatProperty->siatSucursalPuntoVentaActive->sucursal;
            $codigoPuntoVenta = $siatProperty->siatSucursalPuntoVentaActive->punto_venta;

        }
        $config = new SiatConfig([
            'codigoSistema' => $siatProperty->system_code,
            'nit' => $siatProperty->nit,
            'apiKey' => $siatProperty->token,
            'ambiente' => $siatProperty->environment,
            'modalidad' => $siatProperty->modality,
            'codigoSucursal' => $codigoSucursal,
            'codigoPuntoVenta' => $codigoPuntoVenta,
        ]);

        $client = new SiatClient($config);
        $solicitudCuis = new SolicitudCuis(
            codigoPuntoVenta: $codigoPuntoVenta,
            codigoSucursal: $codigoSucursal
        );

        $respuestaCuis = $client->codigos()->cuis($solicitudCuis);

        // Asignamos el CUIS obtenido a nuestra configuración
        Log::info("¡ÉXITO! CUIS obtenido: ");
        Log::info(json_encode($respuestaCuis));

        return $respuestaCuis;
    }

    public function getActividades(SiatProperty $siatProperty)
    {
        $actividades = [];
        $siatSpV = $siatProperty->siatSucursalPuntoVentaActive;
        $config = new SiatConfig([
            'codigoSistema' => $siatProperty->system_code,
            'nit' => $siatProperty->nit,
            'apiKey' => $siatProperty->token,
            'ambiente' => $siatProperty->environment,
            'modalidad' => $siatProperty->modality,
            'codigoSucursal' => $siatSpV->sucursal,
            'codigoPuntoVenta' => $siatSpV->punto_venta,
            'cuis' => $siatSpV->cuis
        ]);

        $client = new SiatClient($config);

        $service = $client->sincronizacion();
        $request = new SolicitudSincronizacion($siatSpV->punto_venta);

        $response = $service->sincronizarActividades($request);

        if ($response->transaccion) {
            $actividades = $response->listaActividades;
            Log::info('Response Actividades: ' . json_encode($response));
        } else {
            Log::info('Error en ' . self::class . ' || ' . __FUNCTION__ . ':: ' . json_encode($response));
            $message = $response->mensajesList[0];
            throw new Exception($message->descripcion, $message->codigo);
        }
        return $actividades;
    }

    public function getActividadesDocumentoSector(SiatProperty $siatProperty)
    {
        $actividades = [];
        $siatSpV = $siatProperty->siatSucursalPuntoVentaActive;
        $config = new SiatConfig([
            'codigoSistema' => $siatProperty->system_code,
            'nit' => $siatProperty->nit,
            'apiKey' => $siatProperty->token,
            'ambiente' => $siatProperty->environment,
            'modalidad' => $siatProperty->modality,
            'codigoSucursal' => $siatSpV->sucursal,
            'codigoPuntoVenta' => $siatSpV->punto_venta,
            'cuis' => $siatSpV->cuis
        ]);

        $client = new SiatClient($config);
        $service = $client->sincronizacion();
        $request = new SolicitudSincronizacion($siatSpV->punto_venta);
        $response = $service->sincronizarActividadesDocumentoSector($request);

        if ($response->transaccion) {
            $actividades = $response->listaActividadesDocumentoSector;
            Log::info('Response Actividades: ' . json_encode($response));
        } else {
            Log::info('Error en ' . self::class . ' || ' . __FUNCTION__ . ':: ' . json_encode($response));
            $message = $response->mensajesList[0];
            throw new Exception($message->descripcion, $message->codigo);
        }
        return $actividades;
    }

    public function getActividadesLeyendas(SiatProperty $siatProperty)
    {
        $leyendas = [];
        $siatSpV = $siatProperty->siatSucursalPuntoVentaActive;
        $config = new SiatConfig([
            'codigoSistema' => $siatProperty->system_code,
            'nit' => $siatProperty->nit,
            'apiKey' => $siatProperty->token,
            'ambiente' => $siatProperty->environment,
            'modalidad' => $siatProperty->modality,
            'codigoSucursal' => $siatSpV->sucursal,
            'codigoPuntoVenta' => $siatSpV->punto_venta,
            'cuis' => $siatSpV->cuis
        ]);

        $client = new SiatClient($config);
        $service = $client->sincronizacion();
        $request = new SolicitudSincronizacion($siatSpV->punto_venta);
        $response = $service->sincronizarListaLeyendasFactura($request);

        if ($response->transaccion) {
            $leyendas = $response->listaLeyendas;
            Log::info('Response Leyendas: ' . json_encode($response));
        } else {
            Log::info('Error en ' . self::class . ' || ' . __FUNCTION__ . ':: ' . json_encode($response));
            $message = $response->mensajesList[0];
            throw new Exception($message->descripcion, $message->codigo);
        }
        return $leyendas;
    }

    public function getProductos(SiatProperty $siatProperty)
    {
        $productos = [];
        $siatSpV = $siatProperty->siatSucursalPuntoVentaActive;
        $config = new SiatConfig([
            'codigoSistema' => $siatProperty->system_code,
            'nit' => $siatProperty->nit,
            'apiKey' => $siatProperty->token,
            'ambiente' => $siatProperty->environment,
            'modalidad' => $siatProperty->modality,
            'codigoSucursal' => $siatSpV->sucursal,
            'codigoPuntoVenta' => $siatSpV->punto_venta,
            'cuis' => $siatSpV->cuis
        ]);

        $client = new SiatClient($config);
        $service = $client->sincronizacion();
        $request = new SolicitudSincronizacion($siatSpV->punto_venta);
        $response = $service->sincronizarListaProductosServicios($request);

        if ($response->transaccion) {
            // Log::info('Response Productos Servicios: ' . json_encode($response));
            $productos = $response->listaCodigos;

        } else {
            Log::info('Error en ' . self::class . ' || ' . __FUNCTION__ . ':: ' . json_encode($response));
            $message = $response->mensajesList[0];
            throw new Exception($message->descripcion, $message->codigo);
        }
        return $productos;
    }

    public function getEventos(SiatProperty $siatProperty)
    {
        $eventos = [];
        $siatSpV = $siatProperty->siatSucursalPuntoVentaActive;
        $config = new SiatConfig([
            'codigoSistema' => $siatProperty->system_code,
            'nit' => $siatProperty->nit,
            'apiKey' => $siatProperty->token,
            'ambiente' => $siatProperty->environment,
            'modalidad' => $siatProperty->modality,
            'codigoSucursal' => $siatSpV->sucursal,
            'codigoPuntoVenta' => $siatSpV->punto_venta,
            'cuis' => $siatSpV->cuis
        ]);

        $client = new SiatClient($config);
        $service = $client->sincronizacion();
        $request = new SolicitudSincronizacion($siatSpV->punto_venta);
        $response = $service->sincronizarParametricaEventosSignificativos($request);

        if ($response->transaccion) {
            $eventos = $response->listaCodigos;
            Log::info('Response Eventos Significativos: ' . json_encode($response));
        } else {
            Log::info('Error en ' . self::class . ' || ' . __FUNCTION__ . ':: ' . json_encode($response));
            $message = $response->mensajesList[0];
            throw new Exception($message->descripcion, $message->codigo);
        }
        return $eventos;
    }

    public function getMotivoAnulaciones(SiatProperty $siatProperty)
    {
        $eventos = [];
        $siatSpV = $siatProperty->siatSucursalPuntoVentaActive;
        $config = new SiatConfig([
            'codigoSistema' => $siatProperty->system_code,
            'nit' => $siatProperty->nit,
            'apiKey' => $siatProperty->token,
            'ambiente' => $siatProperty->environment,
            'modalidad' => $siatProperty->modality,
            'codigoSucursal' => $siatSpV->sucursal,
            'codigoPuntoVenta' => $siatSpV->punto_venta,
            'cuis' => $siatSpV->cuis
        ]);

        $client = new SiatClient($config);
        $service = $client->sincronizacion();
        $request = new SolicitudSincronizacion($siatSpV->punto_venta);
        $response = $service->sincronizarParametricaMotivoAnulacion($request);

        if ($response->transaccion) {
            $eventos = $response->listaCodigos;
            Log::info('Response Motivos anulacion: ' . json_encode($response));
        } else {
            Log::info('Error en ' . self::class . ' || ' . __FUNCTION__ . ':: ' . json_encode($response));
            $message = $response->mensajesList[0];
            throw new Exception($message->descripcion, $message->codigo);
        }
        return $eventos;
    }

    public function getDocumentosIdentidad(SiatProperty $siatProperty)
    {
        $codigos = [];
        $siatSpV = $siatProperty->siatSucursalPuntoVentaActive;
        $config = new SiatConfig([
            'codigoSistema' => $siatProperty->system_code,
            'nit' => $siatProperty->nit,
            'apiKey' => $siatProperty->token,
            'ambiente' => $siatProperty->environment,
            'modalidad' => $siatProperty->modality,
            'codigoSucursal' => $siatSpV->sucursal,
            'codigoPuntoVenta' => $siatSpV->punto_venta,
            'cuis' => $siatSpV->cuis
        ]);

        $client = new SiatClient($config);
        $service = $client->sincronizacion();
        $request = new SolicitudSincronizacion($siatSpV->punto_venta);
        $response = $service->sincronizarParametricaTipoDocumentoIdentidad($request);

        if ($response->transaccion) {
            $codigos = $response->listaCodigos;
            Log::info('Response Tipos Documentos identidad: ' . json_encode($response));
        } else {
            Log::info('Error en ' . self::class . ' || ' . __FUNCTION__ . ':: ' . json_encode($response));
            $message = $response->mensajesList[0];
            throw new Exception($message->descripcion, $message->codigo);
        }
        return $codigos;
    }

    public function getTipoDocumentosSector(SiatProperty $siatProperty)
    {
        $codigos = [];
        $siatSpV = $siatProperty->siatSucursalPuntoVentaActive;
        $config = new SiatConfig([
            'codigoSistema' => $siatProperty->system_code,
            'nit' => $siatProperty->nit,
            'apiKey' => $siatProperty->token,
            'ambiente' => $siatProperty->environment,
            'modalidad' => $siatProperty->modality,
            'codigoSucursal' => $siatSpV->sucursal,
            'codigoPuntoVenta' => $siatSpV->punto_venta,
            'cuis' => $siatSpV->cuis
        ]);

        $client = new SiatClient($config);
        $service = $client->sincronizacion();
        $request = new SolicitudSincronizacion($siatSpV->punto_venta);
        $response = $service->sincronizarParametricaTipoDocumentoSector($request);

        if ($response->transaccion) {
            $codigos = $response->listaCodigos;
            Log::info('Response Tipos Documento Sector: ' . json_encode($response));
        } else {
            Log::info('Error en ' . self::class . ' || ' . __FUNCTION__ . ':: ' . json_encode($response));
            $message = $response->mensajesList[0];
            throw new Exception($message->descripcion, $message->codigo);
        }
        return $codigos;
    }

    public function getTipoEmisiones(SiatProperty $siatProperty)
    {
        $codigos = [];
        $siatSpV = $siatProperty->siatSucursalPuntoVentaActive;
        $config = new SiatConfig([
            'codigoSistema' => $siatProperty->system_code,
            'nit' => $siatProperty->nit,
            'apiKey' => $siatProperty->token,
            'ambiente' => $siatProperty->environment,
            'modalidad' => $siatProperty->modality,
            'codigoSucursal' => $siatSpV->sucursal,
            'codigoPuntoVenta' => $siatSpV->punto_venta,
            'cuis' => $siatSpV->cuis
        ]);

        $client = new SiatClient($config);
        $service = $client->sincronizacion();
        $request = new SolicitudSincronizacion($siatSpV->punto_venta);
        $response = $service->sincronizarParametricaTipoEmision($request);

        if ($response->transaccion) {
            $codigos = $response->listaCodigos;
            Log::info('Response Tipos Emision: ' . json_encode($response));
        } else {
            Log::info('Error en ' . self::class . ' || ' . __FUNCTION__ . ':: ' . json_encode($response));
            $message = $response->mensajesList[0];
            throw new Exception($message->descripcion, $message->codigo);
        }
        return $codigos;
    }

    public function getTipoMetodoPagos(SiatProperty $siatProperty)
    {
        $codigos = [];
        $siatSpV = $siatProperty->siatSucursalPuntoVentaActive;
        $config = new SiatConfig([
            'codigoSistema' => $siatProperty->system_code,
            'nit' => $siatProperty->nit,
            'apiKey' => $siatProperty->token,
            'ambiente' => $siatProperty->environment,
            'modalidad' => $siatProperty->modality,
            'codigoSucursal' => $siatSpV->sucursal,
            'codigoPuntoVenta' => $siatSpV->punto_venta,
            'cuis' => $siatSpV->cuis
        ]);

        $client = new SiatClient($config);
        $service = $client->sincronizacion();
        $request = new SolicitudSincronizacion($siatSpV->punto_venta);
        $response = $service->sincronizarParametricaTipoMetodoPago($request);

        if ($response->transaccion) {
            $codigos = $response->listaCodigos;
            Log::info('Response Tipos Metodo Pago: ' . json_encode($response));
        } else {
            Log::info('Error en ' . self::class . ' || ' . __FUNCTION__ . ':: ' . json_encode($response));
            $message = $response->mensajesList[0];
            throw new Exception($message->descripcion, $message->codigo);
        }
        return $codigos;
    }

    public function getTipoMonedas(SiatProperty $siatProperty)
    {
        $codigos = [];
        $siatSpV = $siatProperty->siatSucursalPuntoVentaActive;
        $config = new SiatConfig([
            'codigoSistema' => $siatProperty->system_code,
            'nit' => $siatProperty->nit,
            'apiKey' => $siatProperty->token,
            'ambiente' => $siatProperty->environment,
            'modalidad' => $siatProperty->modality,
            'codigoSucursal' => $siatSpV->sucursal,
            'codigoPuntoVenta' => $siatSpV->punto_venta,
            'cuis' => $siatSpV->cuis
        ]);

        $client = new SiatClient($config);
        $service = $client->sincronizacion();
        $request = new SolicitudSincronizacion($siatSpV->punto_venta);
        $response = $service->sincronizarParametricaTipoMoneda($request);

        if ($response->transaccion) {
            $codigos = $response->listaCodigos;
            Log::info('Response Tipo Monedas: ' . json_encode($response));
        } else {
            Log::info('Error en ' . self::class . ' || ' . __FUNCTION__ . ':: ' . json_encode($response));
            $message = $response->mensajesList[0];
            throw new Exception($message->descripcion, $message->codigo);
        }
        return $codigos;
    }

    public function getTipoPuntosVenta(SiatProperty $siatProperty)
    {
        $codigos = [];
        $siatSpV = $siatProperty->siatSucursalPuntoVentaActive;
        $config = new SiatConfig([
            'codigoSistema' => $siatProperty->system_code,
            'nit' => $siatProperty->nit,
            'apiKey' => $siatProperty->token,
            'ambiente' => $siatProperty->environment,
            'modalidad' => $siatProperty->modality,
            'codigoSucursal' => $siatSpV->sucursal,
            'codigoPuntoVenta' => $siatSpV->punto_venta,
            'cuis' => $siatSpV->cuis
        ]);

        $client = new SiatClient($config);
        $service = $client->sincronizacion();
        $request = new SolicitudSincronizacion($siatSpV->punto_venta);
        $response = $service->sincronizarParametricaTipoPuntoVenta($request);

        if ($response->transaccion) {
            $codigos = $response->listaCodigos;
            Log::info('Response Tipo Puntos Venta: ' . json_encode($response));
        } else {
            Log::info('Error en ' . self::class . ' || ' . __FUNCTION__ . ':: ' . json_encode($response));
            $message = $response->mensajesList[0];
            throw new Exception($message->descripcion, $message->codigo);
        }
        return $codigos;
    }

    public function getUnidadMedidas(SiatProperty $siatProperty)
    {
        $unidadMedidas = [];
        $siatSpV = $siatProperty->siatSucursalPuntoVentaActive;
        $config = new SiatConfig([
            'codigoSistema' => $siatProperty->system_code,
            'nit' => $siatProperty->nit,
            'apiKey' => $siatProperty->token,
            'ambiente' => $siatProperty->environment,
            'modalidad' => $siatProperty->modality,
            'codigoSucursal' => $siatSpV->sucursal,
            'codigoPuntoVenta' => $siatSpV->punto_venta,
            'cuis' => $siatSpV->cuis
        ]);

        $client = new SiatClient($config);
        $service = $client->sincronizacion();
        $request = new SolicitudSincronizacion($siatSpV->punto_venta);
        $response = $service->sincronizarParametricaUnidadMedida($request);
        if ($response->transaccion) {
            $unidadMedidas = $response->listaCodigos;
            Log::info('Response Tipo Unidad Medida: ' . json_encode($response));
        } else {
            Log::info('Error en ' . self::class . ' || ' . __FUNCTION__ . ':: ' . json_encode($response));
            $message = $response->mensajesList[0];
            throw new Exception($message->descripcion, $message->codigo);
        }
        return $unidadMedidas;
    }

    public function getTipoFacturas(SiatProperty $siatProperty)
    {
        $facturaTipos = [];
        $siatSpV = $siatProperty->siatSucursalPuntoVentaActive;
        $config = new SiatConfig([
            'codigoSistema' => $siatProperty->system_code,
            'nit' => $siatProperty->nit,
            'apiKey' => $siatProperty->token,
            'ambiente' => $siatProperty->environment,
            'modalidad' => $siatProperty->modality,
            'codigoSucursal' => $siatSpV->sucursal,
            'codigoPuntoVenta' => $siatSpV->punto_venta,
            'cuis' => $siatSpV->cuis
        ]);

        $client = new SiatClient($config);
        $service = $client->sincronizacion();
        $request = new SolicitudSincronizacion($siatSpV->punto_venta);
        $response = $service->sincronizarParametricaTiposFactura($request);
        if ($response->transaccion) {
            $facturaTipos = $response->listaCodigos;
            Log::info('Response Tipo Factura: ' . json_encode($response));
        } else {
            Log::info('Error en ' . self::class . ' || ' . __FUNCTION__ . ':: ' . json_encode($response));
            $message = $response->mensajesList[0];
            throw new Exception($message->descripcion, $message->codigo);
        }
        return $facturaTipos;
    }
}
