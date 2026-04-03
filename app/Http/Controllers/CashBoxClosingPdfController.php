<?php

namespace App\Http\Controllers;

use App\Models\CashBoxClosing;
use App\Services\CashClosingService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class CashBoxClosingPdfController extends Controller
{
    public function exportPdf(Request $request, int $cbcId)
    {
        $cashBoxClosing = CashBoxClosing::find($cbcId);
        
        $svc = app(CashClosingService::class);
                        $totals = $svc->computeTotals(
                            closing: $cashBoxClosing,
                            from: null,
                            until: null,
                            userIdFilter: 8,
                        );

        $size = $request->get('size', 'letter');

        $paper = match ($size) {
            'half' => [0, 0, 396, 612],
            'roll' => [0, 0, 226, 800],
            default => 'letter',
        };

        // 1. Generar array de medidas (0.00 a 6.00 en saltos de 0.25)
        $medidas = [];
        for ($i = 0; $i <= 6; $i += 0.25) {
            $medidas[] = number_format($i, 2);
        }

        // 2. Datos para la cabecera (puedes reemplazarlos con tu modelo de BD)
        $data = ['record' => $cashBoxClosing, 'totals' => $totals];

        $view = 'pdf.cash-box-closing-pdf';

        // IMPORTANTE: Cambiado a 'landscape' para que quepan las 26 columnas
        $pdf = Pdf::loadView($view, $data)->setPaper($paper, 'landscape');

        $filename = 'cbc-export-' . ($income_id ?? 'documento') . '.pdf';

        return $pdf->stream($filename);
    }
}
