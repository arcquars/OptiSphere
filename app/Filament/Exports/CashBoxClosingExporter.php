<?php

namespace App\Filament\Exports;

use App\Models\CashBoxClosing;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options;

class CashBoxClosingExporter extends Exporter
{
    protected static ?string $model = CashBoxClosing::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('branch.name')
                ->label('Sucursal'),
            ExportColumn::make('user.name')
                ->label('Usuario'),
            ExportColumn::make('opening_time')
                ->label('Fecha de apertura')
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->format('d/m/Y H:i')),
            ExportColumn::make('closing_time')
                ->label('Fecha de cierra')
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->format('d/m/Y H:i')),
            ExportColumn::make('initial_balance')
                ->label('Balance inicial')
                ->formatStateUsing(fn ($state) => number_format($state, 2) . ' ' . config('cerisier.currency_symbol')),
            ExportColumn::make('expected_balance')
                ->label('Balance sistema')
                ->formatStateUsing(fn ($state) => number_format($state, 2) . ' ' . config('cerisier.currency_symbol')),
            ExportColumn::make('actual_balance')
                ->label('Balance usuario')
                ->formatStateUsing(fn ($state) => number_format($state, 2) . ' ' . config('cerisier.currency_symbol')),
            ExportColumn::make('difference')
                ->label('Diferencia')
                ->formatStateUsing(fn ($state) => number_format($state, 2) . ' ' . config('cerisier.currency_symbol')),
            ExportColumn::make('notes')
                ->label('Nota'),
            ExportColumn::make('status')
                ->label('Pagado')
                ->state(function (CashBoxClosing $record): string {
                    return (strcmp($record->status, "open") == 0) 
                        ? "ABIERTO" 
                        : "CERRADO";
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
        // $options->setColumnWidth(12, 2);
        $options->setColumnWidth(20, 10);
        $options->setColumnWidthForRange(16, 2, 9);
        $options->setColumnWidth(14, 11);
        
        return $options;
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your cash box closing export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
