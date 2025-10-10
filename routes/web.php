<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;
use \Illuminate\Support\Facades\Auth;
use Filament\Facades\Filament;
use \App\Http\Controllers\SalePdfController;

Route::get('/', function () {
//    return redirect('login');
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    $user = Auth::user();

    // Con Spatie: obtenemos el primer rol del usuario
    // (ajusta si quieres priorizar otro orden)
    $role = method_exists($user, 'getRoleNames')
        ? optional($user->getRoleNames())->first()
        : null;

    if (!$role) {
        // Sin rol => decide a dónde enviar (login o algún panel por defecto)
        return redirect()->route('login');
    }

    // Normaliza a ID de panel (coincide con el rol)
    $panelId = str_replace(' ', '_', strtolower($role));

    // Busca el panel por ID y redirige a su path
    $panel = Filament::getPanel($panelId);

    if ($panel) {
        // getPath() ya te da el prefijo correcto del panel (p.ej. "admin", "accountant", etc.)
        return redirect('/' . ltrim($panel->getPath(), '/'));
    }

    // Fallback si el panel no existe con ese ID
    return redirect()->route('login');
})->name('home');

//Route::view('dashboard', 'dashboard')
//    ->middleware(['auth', 'verified'])
//    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    Route::get('/sales/{sale}/receipt-pdf', [SalePdfController::class, 'receipt'])->name('sales.receipt_pdf');
    Route::get('/sales/{sale}/invoice-pdf', [SalePdfController::class, 'invoice'])->name('sales.invoice_pdf');
});

require __DIR__.'/auth.php';
