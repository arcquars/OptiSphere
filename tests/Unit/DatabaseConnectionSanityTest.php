<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;

uses(Tests\TestCase::class);

// Guarda de seguridad: la suite (RefreshDatabase incluido) DEBE apuntar siempre a una base
// de datos de test separada de la de desarrollo. Si esto falla, ningún otro test debe correr.
it('nunca ejecuta los tests contra la base de datos de desarrollo', function (): void {
    expect(config('database.default'))->toBe('mysql')
        ->and(DB::connection()->getDatabaseName())
            ->toBe('filament_testing')
            ->not->toBe('filament');
});
