<?php

use App\Http\Controllers\Auth\DefinirContraseniaController;
use Illuminate\Support\Facades\Route;

/*
| Solo rutas públicas. El login, la recuperación de contraseña, la verificación
| de correo y el logout (POST) los registra Fortify. El panel está en routes/panel.php.
*/

Route::redirect('/', '/login');

Route::middleware(['signed', 'throttle:6,1'])->group(function () {
    Route::get('definir-contrasenia/{user}/{hash}', [DefinirContraseniaController::class, 'create'])
        ->name('password.definir');
    Route::post('definir-contrasenia/{user}/{hash}', [DefinirContraseniaController::class, 'store'])
        ->name('password.definir.store');
});

// Las rutas públicas de validación por QR (validar/inscripcion-examen/{uuid}, validar/comprobante/{uuid})
// se agregan con CU5 y CU7, cuando existan sus tablas.
