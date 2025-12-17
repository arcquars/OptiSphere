<?php

namespace App\DTOs;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Validation\ValidationException;

/**
 * Data Transfer Object para estructurar el payload de la API de creación de facturas.
 */
class EventSiatDto implements Arrayable
{
//     {
//   "sucursal_id": 0,
//   "puntoventa_id": 0,
//   "evento_id": 2,
//   "fecha_inicio": "2022-08-04 13:48:20",
//   "fecha_fin": null,
//   "cafc": null,
//   "cufd_evento": ""
//     }

    public int $sucursal_id;
    public int $puntoventa_id;
    public int $evento_id;
    public string $fecha_inicio;
    public ?string $fecha_fin;
    public ?string $cafc;
    public ?string $cufd_evento;

    /**
     * Constructor del DTO.
     * @param array $data Datos de entrada.
     * @throws ValidationException Si faltan campos requeridos o son de tipo incorrecto.
     */
    public function __construct(array $data)
    {
        $this->sucursal_id = (int) $data['sucursal_id'];
        $this->puntoventa_id = (int) $data['puntoventa_id'];
        $this->evento_id = (int) $data['evento_id'];
        $this->fecha_inicio = (string) $data['fecha_inicio'];
        $this->fecha_fin = isset($data['fecha_fin']) ? (string) $data['fecha_fin'] : null;
        $this->cafc = isset($data['cafc']) ? (string) $data['cafc'] : null;
        $this->cufd_evento = isset($data['cufd_evento']) ? (string) $data['cufd_evento'] : null;
    }
    /**
     * Convierte el DTO a un array asociativo.
     * @return array
     */
    public function toArray(): array
    {
        return [
            'sucursal_id' => $this->sucursal_id,
            'puntoventa_id' => $this->puntoventa_id,
            'evento_id' => $this->evento_id,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'cafc' => $this->cafc,
            'cufd_evento' => $this->cufd_evento,

        ];
    }
}