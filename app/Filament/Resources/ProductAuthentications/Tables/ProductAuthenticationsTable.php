<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductAuthentications\Tables;

use App\Models\ProductAuthentication;
use App\Services\ProductAuthenticationService;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

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
                //
            ])
            ->toolbarActions([
                //
            ]);
    }
}
