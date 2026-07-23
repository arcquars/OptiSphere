<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductAuthentications\Tables;

use App\Filament\FrequentCustomer\Resources\SaleHistory\Tables\SaleHistoryTable;
use App\Models\ProductAuthentication;
use App\Services\ProductAuthenticationService;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductAuthenticationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('frequentCustomer.user.name')
                    ->label('Cliente frecuente')
                    ->searchable(),
                TextColumn::make('cliente')
                    ->label('Datos del cliente que compró')
                    ->searchable(),
                TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable(),
                TextColumn::make('product.id')
                    ->label('Cod')
                    ,
                TextColumn::make('fecha_compra')
                    ->label('Fecha de compra')
                    ->date()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Solicitado el')
                    ->dateTime()
                    ->sortable(),
                ToggleColumn::make('is_authentication')
                    ->label('Aprobar Autentificación')
                    // La escritura se delega al Service: actualiza el booleano y la
                    // traza de auditoría (fecha y admin) en un solo save.
                    ->updateStateUsing(fn (ProductAuthentication $record, $state): ProductAuthentication => app(ProductAuthenticationService::class)
                        ->setApproval($record, (bool) $state))
                    ->afterStateUpdated(function (): void {
                        Notification::make()
                            ->title('Autenticación actualizada correctamente')
                            ->success()
                            ->send();
                    }),
                // El enlace solo se renderiza en las filas aprobadas: visible() ocultaría
                // la columna entera, así que la condición vive en state() y url().
                TextColumn::make('ver_autentificacion')
                    ->label('Ver autentificación')
                    ->state(fn (ProductAuthentication $record): ?string => $record->is_authentication
                        ? 'Ver autentificación'
                        : null)
                    ->placeholder('—')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('primary')
                    ->url(
                        fn (ProductAuthentication $record): ?string => $record->is_authentication
                            ? app(ProductAuthenticationService::class)->buildPublicUrl($record)
                            : null,
                        shouldOpenInNewTab: true,
                    ),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('editar')
                    ->label('Editar')
                    ->icon('heroicon-o-pencil-square')
                    ->color('gray')
                    ->modalHeading('Editar autentificación')
                    // Reutiliza los mismos campos del modal "Autentificar Producto" del
                    // panel Cliente Frecuente, escopados al cliente frecuente del registro.
                    ->schema(fn (ProductAuthentication $record): array => SaleHistoryTable::productAuthenticationFields(
                        (int) $record->frequent_customer_id
                    ))
                    ->fillForm(fn (ProductAuthentication $record): array => $record->only([
                        'product_id', 'cliente', 'fecha_compra',
                        'od_sphere', 'od_cylinder', 'od_axis',
                        'oi_sphere', 'oi_cylinder', 'oi_axis',
                        'add', 'dip',
                    ]))
                    ->action(function (ProductAuthentication $record, array $data): void {
                        app(ProductAuthenticationService::class)->update($record, $data);

                        Notification::make()
                            ->title('Autentificación actualizada')
                            ->success()
                            ->send();
                    }),
                Action::make('descargar_certificado')
                    ->label('Descargar Certificado')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    // Coherente con la columna "Ver autentificación": solo filas aprobadas
                    ->visible(fn (ProductAuthentication $record): bool => $record->is_authentication)
                    //->action(fn (ProductAuthentication $record) => self::generarCertificado($record))
                    ,
            ])
            ->toolbarActions([
                //
            ]);
    }

    /**
     * Genera el certificado de autenticidad como PNG combinando la plantilla,
     * los datos del registro y un código QR con la URL pública de verificación.
     * Fuerza la descarga inmediata en el navegador.
     */
    private static function generarCertificado(ProductAuthentication $record): ?StreamedResponse
    {
        // ------------------------------------------------------------------
        // Coordenadas y ajustes (px sobre la plantilla de 1521x1034).
        // Ajustar estos valores para calzar el texto/QR sobre la plantilla.
        // ------------------------------------------------------------------
        $clienteX = 150;   // "Nombres del cliente" (X)
        $clienteY = 440;   // "Nombres del cliente" (Y, línea base del texto)
        $serieX   = 150;   // "Número de serie" (X)
        $serieY   = 700;   // "Número de serie" (Y, línea base del texto)
        $qrX      = 1095;  // Esquina superior izquierda del QR (X)
        $qrY      = 575;   // Esquina superior izquierda del QR (Y)
        $qrSize   = 285;   // Lado del QR en px (se escala a este tamaño)
        $fontSize = 34;    // Tamaño del texto TTF
        $fontPath = public_path('fonts/roboto_mono/static/RobotoMono-Bold.ttf');
        $plantilla = public_path('img/certification_template.png');

        // Guarda: si falta la plantilla o la fuente, avisar en vez de un 500 opaco
        if (! is_file($plantilla) || ! is_file($fontPath)) {
            Notification::make()
                ->title('No se pudo generar el certificado: falta la plantilla o la fuente.')
                ->danger()
                ->send();

            return null;
        }

        $base = @imagecreatefrompng($plantilla);
        if ($base === false) {
            Notification::make()
                ->title('No se pudo cargar la plantilla del certificado.')
                ->danger()
                ->send();

            return null;
        }

        // Texto blanco: las zonas de escritura de la plantilla tienen fondo oscuro
        $blanco = imagecolorallocate($base, 255, 255, 255);

        // Datos del registro. La plantilla ya rotula los campos, así que se plasma
        // solo el valor. product?->code protege ante un producto eliminado.
        $cliente = (string) $record->cliente;
        $serie = $record->product?->code ?? '—';

        imagettftext($base, $fontSize, 0, $clienteX, $clienteY, $blanco, $fontPath, $cliente);
        imagettftext($base, $fontSize, 0, $serieX, $serieY, $blanco, $fontPath, $serie);

        // QR local (chillerlan) devuelto como recurso GD nativo, sin red ni archivos temporales
        $qrOptions = new QROptions([
            'outputType' => QROutputInterface::GDIMAGE_PNG,
            'returnResource' => true,
            'scale' => 10,
        ]);
        $urlPublica = app(ProductAuthenticationService::class)->buildPublicUrl($record);
        $qr = (new QRCode($qrOptions))->render($urlPublica);

        // Escala el QR al tamaño destino y lo superpone sobre la plantilla
        $qrEscalado = imagescale($qr, $qrSize, $qrSize);
        imagecopy($base, $qrEscalado, $qrX, $qrY, 0, 0, $qrSize, $qrSize);

        // Libera los buffers intermedios del QR
        imagedestroy($qr);
        imagedestroy($qrEscalado);

        // Descarga con limpieza del buffer de la imagen tras emitirla
        return response()->streamDownload(function () use ($base): void {
            imagepng($base);
            imagedestroy($base);
        }, 'certificado-' . $record->id . '.png', ['Content-Type' => 'image/png']);
    }
}
