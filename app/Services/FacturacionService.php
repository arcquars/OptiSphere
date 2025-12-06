<?php

namespace App\Services;


use App\Integrations\Siat\XmlSigner; 
// --- NUEVO IMPORT ---
use App\Integrations\Siat\SiatInvoiceXmlAdapter; 
// --- SIMULACIÓN DE CLASE DE FACTURA ORIGINAL ---
use SinticBolivia\SBFramework\Modules\Invoices\Classes\Siat\Invoices\Hotel;
// ---------------------------------------------
use Amyrit\SiatBoliviaClient\Data\Requests\SolicitudRecepcionFactura;
use Amyrit\SiatBoliviaClient\SiatConfig;
use Amyrit\SiatBoliviaClient\Data\Responses\RespuestaRecepcionFactura; 
use Amyrit\SiatBoliviaClient\Exceptions\SiatException;
use Exception;
use Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;

/**
 * Clase de servicio que orquesta la generación y firma de facturas.
 */
class FacturacionService
{
    // ... (propiedades y constantes) ...
    private XmlSigner $signer;
    private SiatConfig $config; 
    private SiatInvoiceXmlAdapter $xmlAdapter; // Nuevo adaptador

    private const KEY_PEM_PATH = 'keys/siat/branch_0/cerezo_privada_sin_pass.pem'; 
    private const CUF_GENERADO_PREVIAMENTE = 'A7338C26E9762CA5C54A812328A947A221832049A321B34'; 

    public function __construct(XmlSigner $signer, SiatConfig $config, SiatInvoiceXmlAdapter $adapter)
    {
        $this->signer = $signer;
        $this->config = $config;
        $this->xmlAdapter = $adapter; // Inyectar el adaptador
        
        // Simulación: Comprueba que el archivo .pem existe
        if (!\Storage::disk('public')->exists(self::KEY_PEM_PATH)) {
            throw new Exception("ERROR: Archivo PEM no encontrado en Storage/public. No se puede firmar.");
        }
    }

    /**
     * Proceso completo: 1. Generar XML -> 2. Firmar -> 3. Enviar.
     *
     * @param Model $invoiceData El modelo de tu factura de la DB (ej. Sale) con todos los datos.
     * @return RespuestaRecepcionFactura DTO con la respuesta de SIAT.
     * @throws Exception Si la firma falla.
     */
    public function enviarFactura(Model $invoiceData): RespuestaRecepcionFactura
    {
        // 1. Generar el XML utilizando el adaptador de tu código original
        $rawXmlFactura = $this->generarXmlDeFactura($invoiceData); // Pasamos los datos
        
        // 2. FIRMAR EL XML (Paso crucial)
        try {
            $signedXmlFactura = $this->signer->signXml(
                $rawXmlFactura,
                self::KEY_PEM_PATH, 
                null, 
                'Factura' 
            );
        } catch (Exception $e) {
            Log::error("Fallo crítico al firmar el XML de la factura: " . $e->getMessage());
            throw new Exception("Error al firmar la factura: " . $e->getMessage());
        }
        
        // 3. Crear el DTO de Solicitud (listo para GZIP y HASHEAR)
        $requestDto = SolicitudRecepcionFactura::create(
            codigoDocumentoSector: 1, 
            tipoFacturaDocumento: 1,  
            archivoXml: $signedXmlFactura, 
            codigoPuntoVenta: $this->config->codigoPuntoVenta 
        );

        // 4. Llamar al método recepcionFactura de tu biblioteca SIAT
        // Nota: Asumimos que $this->facturacionClient está inicializado en otro lugar o se inyecta.
        // Aquí asumiremos que tienes una forma de obtener el cliente FacturacionService.
        
        // return $this->facturacionClient->recepcionFactura($requestDto); 
        
        // Por ahora, solo devolvemos el DTO para verificar la integración:
        return new RespuestaRecepcionFactura(transaccion: true, codigoDescripcion: "DTO listo para enviar.");
    }

    /**
     * Usa el adaptador para generar el XML base.
     *
     * @param Model $invoiceData
     * @return string XML de factura sin firmar.
     */
    private function generarXmlDeFactura(Model $invoiceData): string
    {
        // 1. Aquí decides qué clase de factura de tu proyecto original usar
        // Ejemplo: Si el código de sector es 1, usa Hotel, si es 2, usa CompraVenta.
        $invoiceClass = Hotel::class; 
        
        // 2. Llamar al adaptador para construir el XML
        return $this->xmlAdapter->generateRawXml(
            $invoiceClass,
            $invoiceData->toArray(), // Pasar los datos del modelo
            self::CUF_GENERADO_PREVIAMENTE,
            $this->config->cufd
        );
    }
}