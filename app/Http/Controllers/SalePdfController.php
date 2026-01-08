<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\MonoInvoiceApiService;
use App\Services\NumberToWords;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class SalePdfController
{
    public function receipt(Request $request, Sale $sale, NumberToWords $ntw)
    {
        $sale->loadMissing([
            'customer',
            'branch',
            'user',
            'items.salable',
            'items.promotion',
            'payments',
        ]);

        $size = $request->get('size', 'letter');

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

    public function invoice(Request $request, Sale $sale)
    {
        $pSize = $pSize = $request->get('size', null);
        
        // 1. Verificar si el ID de la factura SIAT existe
        if (empty($sale->siat_invoice_id)) {
            return back()->with('error', 'Esta venta no tiene un ID de factura SIAT asociado.');
        }

        // 2. Instanciar el servicio (Asumimos que el modelo Sale tiene la relación 'branch')
        $monoInvoiceService = new MonoInvoiceApiService($sale->branch);
        
        // 3. Llamar al servicio para obtener el PDF en Base64
        $base64Pdf = $monoInvoiceService->pdfInvoice(invoiceId: $sale->siat_invoice_id, tpl: $pSize);

        if (empty($base64Pdf)) {
            Log::error("API MonoInvoice no devolvió el PDF para la factura ID: {$sale->siat_invoice_id}");
            return back()->with('error', 'No se pudo obtener el PDF de la factura desde el servicio externo.');
        }

        // 4. Decodificar el contenido Base64 a binario
        $pdfContent = base64_decode($base64Pdf['buffer'] ?? '');
        
        // 5. Configurar la respuesta de descarga
        $filename = "factura_{$sale->siat_invoice_id}.pdf";
        
        return Response::make($pdfContent, 200, [
            // Indica el tipo de contenido que se está enviando (PDF)
            'Content-Type' => 'application/pdf',
            
            // CRÍTICO: Indica al navegador cómo manejar el archivo
            // 'inline' (default) = Abre en la pestaña del navegador
            // 'attachment' = Fuerza la descarga
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            
            // Asegura que el tamaño del archivo se comunica correctamente
            'Content-Length' => strlen($pdfContent),
        ]);
    }

}
