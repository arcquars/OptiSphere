<?php

namespace App\Interfaces;
interface SiatUnidadeMedidaDAO
{
    /**
     * Obtiene la lista de tipos de documentos de sector desde la fuente de datos (API).
     *
     * @return array|null Un array de objetos o un array asociativo con la lista de códigos.
     */
    public function getUnidadesMedida(): ?array;

}