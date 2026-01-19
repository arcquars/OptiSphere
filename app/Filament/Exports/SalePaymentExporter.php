<?php

namespace App\Filament\Exports;

use App\Models\SalePayment;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;
use Carbon\Carbon;

use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options;

class SalePaymentExporter extends Exporter
{
    protected static ?string $model = SalePayment::class;

    public static function getColumns(): array
    {
        return [
            // 1. Personalizar Etiquetas (Labels)
            ExportColumn::make('id')
                ->label('ID'),

            ExportColumn::make('sale.customer.name')
                ->label('Cliente'),

            // 2. Formatear Moneda
            ExportColumn::make('sale.final_total')
                ->label('Monto Venta')
                ->formatStateUsing(fn ($state) => number_format($state, 2) . ' ' . config('cerisier.currency_symbol')),
            ExportColumn::make('sale.date_sale')
                ->label('Fecha de venta')
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->format('d/m/Y')),
            ExportColumn::make('created_at')
                ->label('Fecha de pago')
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->format('d/m/Y H:i')),
            ExportColumn::make('amount')
                ->label('Monto Pago')
                ->formatStateUsing(fn ($state) => number_format($state, 2) . ' ' . config('cerisier.currency_symbol')),
            ExportColumn::make('payment_method')
                ->label('Método de Pago'),
            ExportColumn::make('branch.name')
                ->label('Sucursal'),

            ExportColumn::make('residue')
                ->label('Saldo Restante')
                ->formatStateUsing(fn ($state) => number_format($state, 2) . ' ' . config('cerisier.currency_symbol')),

            ExportColumn::make('user.name')
                ->label('Usuario'),

            
            // 5. Columnas calculadas o con lógica compleja
            ExportColumn::make('referencia_completa')
                ->label('Pagado')
                ->state(function (SalePayment $record): string {
                    return $record->residue === 0 
                        ? "SI" 
                        : "NO";
                }),
        ];
    }

    public function getXlsxHeaderCellStyle(): ?Style
    {
        return (new Style())
            ->setFontBold()
            ->setFontItalic()
            ->setFontSize(12)
            ->setFontName('Consolas')
            ->setFontColor(Color::rgb(255,255,255))
            ->setBackgroundColor(Color::rgb(179, 63, 0))
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER);
    }

    public function getXlsxWriterOptions(): ?Options
    {
        $options = new Options();
        $options->setColumnWidth(5, 1);
        $options->setColumnWidth(12, 2);
        // $options->setColumnWidth(14, 3);
        $options->setColumnWidthForRange(16, 3, 11);
        
        return $options;
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'La exportación de pagos ha finalizado y se han procesado ' . Number::format($export->successful_rows) . ' registros.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' Lamentablemente, ' . Number::format($failedRowsCount) . ' registros fallaron.';
        }

        return $body;
    }
}