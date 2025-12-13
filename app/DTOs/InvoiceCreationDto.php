<?php

namespace App\DTOs;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Validation\ValidationException;

/**
 * Data Transfer Object para estructurar el payload de la API de creación de facturas.
 */
class InvoiceCreationDto implements Arrayable
{
    /** @var int */
    public string $customerId;

    /** @var string */
    public string $customer;

    // /** @var string Fecha de emisión de la factura (YYYY-MM-DDThh:mm:ss.000). */
    // public string $fechaEmision;
    
    /** @var string */
    public string $nitRucNif;

    /** @var float */
    public float $subTotal;

    /** @var float */
    public float $totalTax;

    /** @var string */
    public string $discount;

    /** @var string */
    public string $montoGiftcard;

    /** @var float */
    public float $total;

    /** @var string */
    public string $invoiceDateTime;

    /** @var string */
    public string $currencyCode;

    /** @var string Código de la Sucursal. */
    public string $codigoSucursal;
    
    /** @var string Código del Punto de Venta. */
    public string $puntoVenta;
    
    /** @var int */
    public int $codigoDocumentoSector;

    /** @var int */
    public int $tipoDocumentoIdentidad;

    /** @var int */
    public int $codigoMetodoPago;

    /** @var int */
    public int $codigoMoneda;

    /** @var string */
    public string $complemento;

    /** @var string */
    public string $numeroTarjeta;

    /** @var int */
    public int $tipoCambio;

    /** @var int */
    public int $tipoFacturaDocumento;

    /** @var array */
    public array $items;

    /** @var string */
    public string $data;

    /**
     * Constructor del DTO.
     * @param array $data Datos de entrada.
     * @throws ValidationException Si faltan campos requeridos o son de tipo incorrecto.
     */
    public function __construct(array $data)
    {
        $required = [
            'customerId', 'customer', 'nitRucNif', 
            'codigoDocumentoSector', 'tipoDocumentoIdentidad', 'codigoMetodoPago', 'codigoMoneda','items'
        ];

        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new ValidationException(null, "El campo requerido '{$field}' está ausente.");
            }
        }

        $this->customerId = (string) $data['customerId'];
        $this->customer = $data['customer'];
        $this->nitRucNif = $data['nitRucNif'];  
        $this->subTotal = (float) $data['subTotal'] ?? 0.0;
        $this->totalTax = (float) $data['totalTax'] ?? 0.0;
        $this->discount = (string) ($data['discount'] ?? '0.00');
        $this->montoGiftcard = (string) ($data['montoGiftcard'] ?? '0.00');
        $this->total = (float) $data['total'] ?? 0.0;
        $this->invoiceDateTime = $data['invoiceDateTime'] ?? now()->toIso8601String();
        $this->currencyCode = $data['currencyCode'] ?? 'BOB';
        $this->codigoSucursal = $data['codigoSucursal'];
        $this->puntoVenta = $data['puntoVenta'];
        $this->codigoDocumentoSector = (int) $data['codigoDocumentoSector'];
        $this->tipoDocumentoIdentidad = (int) $data['tipoDocumentoIdentidad'];
        $this->codigoMetodoPago = (int) $data['codigoMetodoPago'];
        $this->codigoMoneda = (int) $data['codigoMoneda'];
        $this->complemento = $data['complemento'] ?? '';
        $this->numeroTarjeta = $data['numeroTarjeta'] ?? '';
        $this->tipoCambio = (int) ($data['tipoCambio'] ?? 1);
        $this->tipoFacturaDocumento = (int) ($data['tipoFacturaDocumento'] ?? 1);
        $this->data = $data['data'] ?? '{}';

        // Asumiendo que el 'detalle' es un array de objetos/arrays.
        if (!is_array($data['items']) || empty($data['items'])) {
             throw new ValidationException(null, "El campo 'items' debe ser un array no vacío de ítems.");
        }
        $this->items = $data['items'];
    }

    /**
     * Convierte el DTO a un array para ser enviado como payload JSON.
     * @return array
     */
    public function toArray(): array
    {
        // El cuerpo final debe coincidir con la estructura exacta que espera la API
        return [
            'customer_id' => $this->customerId,
            'customer' => $this->customer,
            'nit_ruc_nif' => $this->nitRucNif,
            'sub_total' => $this->subTotal,
            'total_tax' => $this->totalTax,
            'discount' => $this->discount,
            'monto_giftcard' => $this->montoGiftcard,
            'total' => $this->total,
            'invoice_date_time' => $this->invoiceDateTime,
            'currency_code' => $this->currencyCode,
            'codigo_sucursal' => $this->codigoSucursal,
            'punto_venta' => $this->puntoVenta,
            'codigo_documento_sector' => $this->codigoDocumentoSector,
            'tipo_documento_identidad' => $this->tipoDocumentoIdentidad,
            'codigo_metodo_pago' => $this->codigoMetodoPago,
            'codigo_moneda' => $this->codigoMoneda,
            'complemento' => $this->complemento,
            'numero_tarjeta' => $this->numeroTarjeta,
            'tipo_cambio' => $this->tipoCambio,
            'tipo_factura_documento' => $this->tipoFacturaDocumento,
            'items' => $this->items,
            'data' => $this->data,
        ];
    }
}