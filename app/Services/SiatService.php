<?php

namespace App\Services;

use Amyrit\SiatBoliviaClient\Data\Requests\SolicitudSincronizacion;
use Amyrit\SiatBoliviaClient\Exceptions\SiatException;
use Amyrit\SiatBoliviaClient\SiatClient;
use Amyrit\SiatBoliviaClient\SiatConfig;
use App\Models\SiatProperty;
use \Amyrit\SiatBoliviaClient\Data\Requests\SolicitudCuis;
use Exception;
use Log;

class SiatService
{

    public function validConfig(SiatProperty $siatProperty): bool{
        $result = false;
        $config = new SiatConfig([
            'codigoSistema'    => $siatProperty->system_code,
            'nit'              => $siatProperty->nit,
            'apiKey'           => $siatProperty->token,
            'ambiente'         => $siatProperty->environment,
            'modalidad'        => $siatProperty->modality,
            'codigoSucursal'   => 0,
            'codigoPuntoVenta' => 0,
        ]);

        $client = new SiatClient($config);
        try {
            $solicitudCuis = new SolicitudCuis(
                codigoPuntoVenta: 0,
                codigoSucursal: 0
            );

            $respuestaCuis = $client->codigos()->cuis($solicitudCuis);

            // Asignamos el CUIS obtenido a nuestra configuración
            $config->cuis = $respuestaCuis->codigoCuis;
            $result = true;
        } catch (SiatException $e) {
            $result = false;
        } catch (\Exception $e) {
            $result = false;
        } finally {
            return $result;
        }
    }

    public function getCuis(SiatProperty $siatProperty, $codigoSucursal=0, $codigoPuntoVenta=0){
        $config = new SiatConfig([
            'codigoSistema'    => $siatProperty->system_code,
            'nit'              => $siatProperty->nit,
            'apiKey'           => $siatProperty->token,
            'ambiente'         => $siatProperty->environment,
            'modalidad'        => $siatProperty->modality,
            'codigoSucursal'   => $codigoSucursal,
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

    public function getActividades(SiatProperty $siatProperty){
        $actividades = [];
        $siatSpV = $siatProperty->siatSucursalPuntoVentaActive;
        $config = new SiatConfig([
            'codigoSistema'    => $siatProperty->system_code,
            'nit'              => $siatProperty->nit,
            'apiKey'           => $siatProperty->token,
            'ambiente'         => $siatProperty->environment,
            'modalidad'        => $siatProperty->modality,
            'codigoSucursal'   => $siatSpV->sucursal,
            'codigoPuntoVenta' => $siatSpV->punto_venta,
            'cuis'             => $siatSpV->cuis
        ]);

        $client = new SiatClient($config);
        
        $service = $client->sincronizacion();
        $request = new SolicitudSincronizacion($siatSpV->punto_venta);

        $response = $service->sincronizarActividades($request);

        if ($response->transaccion) {
            $actividades = $response->listaActividades;
            Log::info('Response Actividades: ' . json_encode($response));
        } else {
            Log::info('Error en '. self::class .' || ' . __FUNCTION__ . ':: ' . json_encode($response));
            $message = $response->mensajesList[0];
            throw new Exception($message->descripcion, $message->codigo);
        }
        return $actividades;
    }

    public function getActividadesDocumentoSector(SiatProperty $siatProperty){
        $actividades = [];
        $siatSpV = $siatProperty->siatSucursalPuntoVentaActive;
        $config = new SiatConfig([
            'codigoSistema'    => $siatProperty->system_code,
            'nit'              => $siatProperty->nit,
            'apiKey'           => $siatProperty->token,
            'ambiente'         => $siatProperty->environment,
            'modalidad'        => $siatProperty->modality,
            'codigoSucursal'   => $siatSpV->sucursal,
            'codigoPuntoVenta' => $siatSpV->punto_venta,
            'cuis'             => $siatSpV->cuis
        ]);

        $client = new SiatClient($config);
        $service = $client->sincronizacion();
        $request = new SolicitudSincronizacion($siatSpV->punto_venta);
        $response = $service->sincronizarActividadesDocumentoSector($request);

        if ($response->transaccion) {
            $actividades = $response->listaActividadesDocumentoSector;
            Log::info('Response Actividades: ' . json_encode($response));
        } else {
            Log::info('Error en '. self::class .' || ' . __FUNCTION__ . ':: ' . json_encode($response));
            $message = $response->mensajesList[0];
            throw new Exception($message->descripcion, $message->codigo);
        }
        return $actividades;
    }

    public function getActividadesLeyendas(SiatProperty $siatProperty){
        $leyendas = [];
        $siatSpV = $siatProperty->siatSucursalPuntoVentaActive;
        $config = new SiatConfig([
            'codigoSistema'    => $siatProperty->system_code,
            'nit'              => $siatProperty->nit,
            'apiKey'           => $siatProperty->token,
            'ambiente'         => $siatProperty->environment,
            'modalidad'        => $siatProperty->modality,
            'codigoSucursal'   => $siatSpV->sucursal,
            'codigoPuntoVenta' => $siatSpV->punto_venta,
            'cuis'             => $siatSpV->cuis
        ]);

        $client = new SiatClient($config);
        $service = $client->sincronizacion();
        $request = new SolicitudSincronizacion($siatSpV->punto_venta);
        $response = $service->sincronizarListaLeyendasFactura($request);

        if ($response->transaccion) {
            $leyendas = $response->listaLeyendas;
            Log::info('Response Leyendas: ' . json_encode($response));
        } else {
            Log::info('Error en '. self::class .' || ' . __FUNCTION__ . ':: ' . json_encode($response));
            $message = $response->mensajesList[0];
            throw new Exception($message->descripcion, $message->codigo);
        }
        return $leyendas;
    }

    public function getProductos(SiatProperty $siatProperty){
        $productos = [];
        $siatSpV = $siatProperty->siatSucursalPuntoVentaActive;
        $config = new SiatConfig([
            'codigoSistema'    => $siatProperty->system_code,
            'nit'              => $siatProperty->nit,
            'apiKey'           => $siatProperty->token,
            'ambiente'         => $siatProperty->environment,
            'modalidad'        => $siatProperty->modality,
            'codigoSucursal'   => $siatSpV->sucursal,
            'codigoPuntoVenta' => $siatSpV->punto_venta,
            'cuis'             => $siatSpV->cuis
        ]);

        $client = new SiatClient($config);
        $service = $client->sincronizacion();
        $request = new SolicitudSincronizacion($siatSpV->punto_venta);
        $response = $service->sincronizarListaProductosServicios($request);

        if ($response->transaccion) {
            // Log::info('Response Productos Servicios: ' . json_encode($response));
            $productos = $response->listaCodigos;
            
        } else {
            Log::info('Error en '. self::class .' || ' . __FUNCTION__ . ':: ' . json_encode($response));
            $message = $response->mensajesList[0];
            throw new Exception($message->descripcion, $message->codigo);
        }
        return $productos;
    }

    public function getEventos(SiatProperty $siatProperty){
        $eventos = [];
        $siatSpV = $siatProperty->siatSucursalPuntoVentaActive;
        $config = new SiatConfig([
            'codigoSistema'    => $siatProperty->system_code,
            'nit'              => $siatProperty->nit,
            'apiKey'           => $siatProperty->token,
            'ambiente'         => $siatProperty->environment,
            'modalidad'        => $siatProperty->modality,
            'codigoSucursal'   => $siatSpV->sucursal,
            'codigoPuntoVenta' => $siatSpV->punto_venta,
            'cuis'             => $siatSpV->cuis
        ]);

        $client = new SiatClient($config);
        $service = $client->sincronizacion();
        $request = new SolicitudSincronizacion($siatSpV->punto_venta);
        $response = $service->sincronizarParametricaEventosSignificativos($request);

        if ($response->transaccion) {
            $eventos = $response->listaCodigos;
            Log::info('Response Eventos Significativos: ' . json_encode($response));
        } else {
            Log::info('Error en '. self::class .' || ' . __FUNCTION__ . ':: ' . json_encode($response));
            $message = $response->mensajesList[0];
            throw new Exception($message->descripcion, $message->codigo);
        }
        return $eventos;
    }

    public function getMotivoAnulaciones(SiatProperty $siatProperty){
        $eventos = [];
        $siatSpV = $siatProperty->siatSucursalPuntoVentaActive;
        $config = new SiatConfig([
            'codigoSistema'    => $siatProperty->system_code,
            'nit'              => $siatProperty->nit,
            'apiKey'           => $siatProperty->token,
            'ambiente'         => $siatProperty->environment,
            'modalidad'        => $siatProperty->modality,
            'codigoSucursal'   => $siatSpV->sucursal,
            'codigoPuntoVenta' => $siatSpV->punto_venta,
            'cuis'             => $siatSpV->cuis
        ]);

        $client = new SiatClient($config);
        $service = $client->sincronizacion();
        $request = new SolicitudSincronizacion($siatSpV->punto_venta);
        $response = $service->sincronizarParametricaMotivoAnulacion($request);

        if ($response->transaccion) {
            $eventos = $response->listaCodigos;
            Log::info('Response Motivos anulacion: ' . json_encode($response));
        } else {
            Log::info('Error en '. self::class .' || ' . __FUNCTION__ . ':: ' . json_encode($response));
            $message = $response->mensajesList[0];
            throw new Exception($message->descripcion, $message->codigo);
        }
        return $eventos;
    }
}
