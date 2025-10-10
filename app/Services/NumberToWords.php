<?php

namespace App\Services;

use InvalidArgumentException;

class NumberToWords
{
    /**
     * Convierte un número a letras en español (entero).
     * Soporta hasta billones (ajusta si necesitas más).
     */
    public function toSpanish(int $number): string
    {
        if ($number === 0) {
            return 'cero';
        }
        if ($number < 0) {
            return 'menos ' . $this->toSpanish(abs($number));
        }

        $units = [
            '', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve',
            'diez', 'once', 'doce', 'trece', 'catorce', 'quince',
            'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve',
            'veinte', 'veintiuno', 'veintidós', 'veintitrés', 'veinticuatro', 'veinticinco',
            'veintiséis', 'veintisiete', 'veintiocho', 'veintinueve'
        ];

        $tens = ['', '', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
        $hundreds = [
            '', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos',
            'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'
        ];

        $words = '';

        $billions = intdiv($number, 1000000000);
        $number %= 1000000000;

        $millions = intdiv($number, 1000000);
        $number %= 1000000;

        $thousands = intdiv($number, 1000);
        $number %= 1000;

        $remainder = $number;

        if ($billions > 0) {
            $words .= $this->chunkToSpanish($billions, $units, $tens, $hundreds) . ' ' . ($billions === 1 ? 'mil millones' : 'mil millones'); // opción: “un millardo”
            $words .= $millions + $thousands + $remainder > 0 ? ' ' : '';
        }

        if ($millions > 0) {
            if ($millions === 1) {
                $words .= 'un millón';
            } else {
                $words .= $this->chunkToSpanish($millions, $units, $tens, $hundreds) . ' millones';
            }
            $words .= $thousands + $remainder > 0 ? ' ' : '';
        }

        if ($thousands > 0) {
            if ($thousands === 1) {
                $words .= 'mil';
            } else {
                $words .= $this->chunkToSpanish($thousands, $units, $tens, $hundreds) . ' mil';
            }
            $words .= $remainder > 0 ? ' ' : '';
        }

        if ($remainder > 0) {
            if ($remainder === 100) {
                $words .= 'cien';
            } else {
                $words .= $this->chunkToSpanish($remainder, $units, $tens, $hundreds);
            }
        }

        // Limpieza: “uno” → “un” cuando va seguido de sustantivo masculino (lo haremos en moneda)
        return trim($words);
    }

    /**
     * Convierte montos decimales a letras + moneda.
     * Ej: 123.45 -> "CIENTO VEINTITRÉS  BOLIVIANOS CON 45/100"
     */
    public function toSpanishWithCurrency(float|string $amount, string $currency = 'BOLIVIANOS'): string
    {
        if (!is_numeric($amount)) {
            throw new InvalidArgumentException('El monto debe ser numérico.');
        }

        $amount = (float) $amount;

        $integer = (int) floor($amount + 0.0000001);
        $cents   = (int) round(($amount - $integer) * 100);

        $enteroEnLetras = $this->toSpanish($integer);

        // “uno” -> “un” para concordar con masculino “bolivianos”
        $enteroEnLetras = preg_replace('/\buno\b/u', 'un', $enteroEnLetras);

        // Mayúsculas (estilo típico en factura)
        $enteroEnLetras = mb_strtoupper($enteroEnLetras, 'UTF-8');

        $centavos = str_pad((string) $cents, 2, '0', STR_PAD_LEFT) . '/100';

        // Singular/plural moneda
        $currency = mb_strtoupper($currency, 'UTF-8');
        $currencyWord = ($integer === 1) ? rtrim($currency, 'S') : $currency; // “BOLIVIANO” vs “BOLIVIANOS”

        return sprintf('%s %s CON %s', $enteroEnLetras, $currencyWord, $centavos);
    }

    /**
     * Convierte números 1..999 a letras usando tablas.
     */
    private function chunkToSpanish(int $n, array $units, array $tens, array $hundreds): string
    {
        $out = '';

        $c = intdiv($n, 100);
        $n %= 100;

        if ($c > 0) {
            $out .= $hundreds[$c];
            if ($n > 0) $out .= ' ';
        }

        if ($n > 0) {
            if ($n < 30) {
                $out .= $units[$n];
            } else {
                $t = intdiv($n, 10);
                $u = $n % 10;
                $out .= $tens[$t];
                if ($u > 0) {
                    $out .= ' y ' . $units[$u];
                }
            }
        }

        return $out;
    }
}
