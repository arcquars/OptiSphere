<?php

use App\Http\Controllers\CashBoxClosingPdfController;
use App\Http\Controllers\ExportPdfController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Filament\Facades\Filament;
use App\Http\Controllers\SalePdfController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

/**
 * RUTA RAÍZ OPTIMIZADA
 * 1. Si el usuario NO está autenticado, muestra la Landing Page (welcome).
 * 2. Si ESTÁ autenticado, redirige automáticamente a su panel de Filament.
 */
Route::get('/', function () {
    if (!Auth::check()) {
        // En lugar de redirigir a login, mostramos nuestra nueva Landing Page
        return view('welcome');
    }

    $user = Auth::user();

    // Lógica de redirección por roles (Spatie)
    $role = method_exists($user, 'getRoleNames')
        ? optional($user->getRoleNames())->first()
        : null;

    if (!$role) {
        return redirect()->route('login');
    }

    $panelId = str_replace(' ', '_', strtolower($role));

    try {
        $panel = Filament::getPanel($panelId);
        if ($panel) {
            return redirect('/' . ltrim($panel->getPath(), '/'));
        }
    } catch (\Exception $e) {
        // En caso de error de panel, enviamos al login seguro
        return redirect()->route('login');
    }

    return redirect()->route('login');
})->name('home');

/*
|--------------------------------------------------------------------------
| Rutas Autenticadas
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    Route::get('/sales/{sale}/receipt-pdf', [SalePdfController::class, 'receipt'])->name('sales.receipt_pdf');
    Route::get('/sales/{sale}/invoice-pdf', [SalePdfController::class, 'invoice'])->name('sales.invoice_pdf');

    Route::get('/cash-box-closing/{cbcId}/export-pdf', [CashBoxClosingPdfController::class, 'exportPdf'])->name('cahsboxclosing.export.pdf');

    Route::get('/export-pdf/history/{movement}/{movement_id}/{type}', [ExportPdfController::class, 'historyByMovement'])->name('export.pdf.history.movement');
    Route::get('/export-pdf/cash-movement/{cash_movement_id}', [ExportPdfController::class, 'cashMovement'])->name('export.pdf.cash.movement');
    Route::get('/export-pdf/saldo-warehouse/{warehouseId}/{codeBase}/{type}', [ExportPdfController::class, 'saldoByWarehouse'])->name('export.pdf.saldo.warehouse');
});

require __DIR__.'/auth.php';