<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\NumberToWords;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class SalePdfController
{
    public function receipt(Request $request, Sale $sale, NumberToWords $ntw)
    {
        // Eager-load para evitar N+1 y tener todo listo para la vista PDF
        $sale->loadMissing([
            'customer',
            'branch',
            'user',
            'items.salable',
            'items.promotion',
            'payments',
        ]);

        // Tamaños: letter (default), half (media carta), roll (rollo/ticket)
        $size = $request->get('size', 'letter');

        // Mapea tamaños a DomPDF
        // letter: 612x792 pt (8.5x11")
        // half:   396x612 pt (5.5x8.5") -> media carta
        // roll:   226x800 pt aprox. (80mm x largo variable); ajusta el alto según tu contenido
        $paper = match ($size) {
            'half' => [0, 0, 396, 612],
            'roll' => [0, 0, 226, 800], // ajusta el alto si necesitas más/menos
            default => 'letter',
        };

        $total = $sale->total
            ?? $sale->total_amount
            ?? $sale->items->sum(fn($i) => ($i->final_price_per_unit ?? $i->price ?? 0) * ($i->quantity ?? 1) - ($i->discount ?? 0));

        $amountInWords = $ntw->toSpanishWithCurrency($total, 'BOLIVIANOS');

        $view = 'pdf.sale-receipt';
        if(strcmp($size, 'roll') == 0 ){
            $view = 'pdf.sale-receipt-roll';
        }

        $pdf = Pdf::loadView($view, [
            'sale' => $sale,
            'size' => $size,
            'amountInWords'  => $amountInWords
        ])->setPaper($paper, 'portrait');

        $filename = 'venta-recibo-' . ($sale->id ?? 'documento') . '.pdf';

        return $pdf->stream($filename); // o ->download($filename)
    }

    public function invoice(Request $request, Sale $sale, NumberToWords $ntw)
    {
        // Eager-load para evitar N+1 y tener todo listo para la vista PDF
        $sale->loadMissing([
            'customer',
            'branch',
            'user',
            'items.salable',
            'items.promotion',
            'payments',
        ]);

        // Tamaños: letter (default), half (media carta), roll (rollo/ticket)
        $size = $request->get('size', 'letter');

        // Mapea tamaños a DomPDF
        // letter: 612x792 pt (8.5x11")
        // half:   396x612 pt (5.5x8.5") -> media carta
        // roll:   226x800 pt aprox. (80mm x largo variable); ajusta el alto según tu contenido
        $paper = match ($size) {
            'half' => [0, 0, 396, 612],
            'roll' => [0, 0, 226, 800], // ajusta el alto si necesitas más/menos
            default => 'letter',
        };

        $total = $sale->total
            ?? $sale->total_amount
            ?? $sale->items->sum(fn($i) => ($i->final_price_per_unit ?? $i->price ?? 0) * ($i->quantity ?? 1) - ($i->discount ?? 0));

        $amountInWords = $ntw->toSpanishWithCurrency($total, 'BOLIVIANOS');
        $pdf = Pdf::loadView('pdf.sale-invoice', [
            'sale' => $sale,
            'size' => $size,
            'amountInWords'  => $amountInWords
        ])->setPaper($paper, 'portrait');

        $filename = 'venta-' . ($sale->id ?? 'documento') . '.pdf';

        return $pdf->stream($filename); // o ->download($filename)
    }

}
