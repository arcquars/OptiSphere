<?php

namespace App\Http\Controllers;

use App\Models\OpticalProperty;
use App\Models\User;
use App\Models\WarehouseDelivery;
use App\Models\WarehouseIncome;
use App\Models\WarehouseRefund;
use App\Models\WarehouseStockHistory;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class ExportPdfController extends Controller
{
    public function historyByMovement(Request $request, $movement, $movement_id, $type)
    {
        $warehouseM = null;
        $bgAction = "";
        switch($movement){
            case "INGRESO":
                $warehouseM = WarehouseIncome::find($movement_id);
                $bgAction = "success";
                break;
            case "ENTREGA":
                $warehouseM = WarehouseDelivery::find($movement_id);
                $bgAction = "info";
                break;
            default:
                $warehouseM = WarehouseRefund::find($movement_id);
                $bgAction = "warning";
                break;
        }

        $userM = User::find($warehouseM->user_id);

        $warehouseStockHistories = WarehouseStockHistory::where('movement_type', 'like', "%".$movement."%")
                ->where('type_id', $warehouseM->id) // Asumiendo que usas las propiedades de tu clase
                ->get();

        // dd($warehouseStockHistories);
        $opticalProperties = OpticalProperty::where('base_code', $warehouseM->base_code)
                ->where('type', $type)
                ->whereHas('product', function ($query){
                    $query->where('is_active', true);
                })
                ->get();
        $uniqueSpheres = $opticalProperties->pluck('sphere')->unique()->sort()->values();
        $uniqueCylinders = $opticalProperties->pluck('cylinder')->unique()->sort()->values();

        $matrix = [];
        $this->loadCylinders($warehouseM, $type, $uniqueSpheres, $uniqueCylinders, $matrix, $warehouseStockHistories);

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
        $data = [
            'warehouseM' => $warehouseM, 
            'type' => $type, 
            'uniqueSpheres' => $uniqueSpheres, 
            'uniqueCylinders' => $uniqueCylinders, 
            'matrix' => $matrix, 
            'warehouseStockHistories' => $warehouseStockHistories,
            'bgAction' => $bgAction,
            'registrado_por' => $userM->name,
            'codigo' => $warehouseM->base_code,
            'fecha' => $warehouseM->created_at,
            'accion' => $movement
        ];

        $view = 'export-pdf.history-movement';

        // IMPORTANTE: Cambiado a 'landscape' para que quepan las 26 columnas
        $pdf = Pdf::loadView($view, $data)->setPaper($paper, 'landscape');

        $filename = 'history-income-' . ($income_id ?? 'documento') . '.pdf';

        return $pdf->stream($filename);
    }

    private function loadCylinders($warehouseM, $type, $uniqueSpheres, $uniqueCylinders, &$matrix, &$warehouseStockHistories){
        if($warehouseM->base_code){
            foreach ($uniqueSpheres as $sphere){
                $row = [];
                foreach ($uniqueCylinders as $cylinder){
                    /** @var OpticalProperty $op */
                    $op = OpticalProperty::where('base_code', $warehouseM->base_code)
                        ->where('type', $type)
                        ->where('sphere', $sphere)
                        ->where('cylinder', $cylinder)
                        ->first();

                    $amount = null;
                    $description = "";
                    foreach($warehouseStockHistories as $whsh){
                        if($whsh->warehouseStock->product->id == $op->product_id){                            
                            //$amount = $whsh->warehouseStock->quantity . "xx";
                            $amount = $whsh->difference;
                        }
                    }
                    
                    $row[] = [
                        'id' => $op->product_id,
                        'type' => $op->type,
                        'sphere' => $op->sphere,
                        'cylinder' => $op->cylinder,
                        'description' => $description,
                        'amount' => $amount];

                }
                $matrix[] = $row;
            }
        }
    }
}
