<?php

namespace App\Interfaces;

use App\DTOs\InvoiceCreationDto;

interface MonoInvoiceApiInterface
{
    /**
     * Crea una nueva factura utilizando el servicio externo.
     *
     * @param InvoiceCreationDto $invoiceData El DTO con todos los datos de la factura.
     * @return array|null La respuesta exitosa de la API, o null en caso de fallo.
     */
    public function createInvoice(InvoiceCreationDto $invoiceData): ?array;
}