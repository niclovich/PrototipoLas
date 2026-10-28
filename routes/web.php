<?php

use App\Http\Controllers\Auth\DefinirContraseniaController;
use App\Http\Controllers\Publico\ValidarComprobanteController;
use App\Http\Controllers\Publico\ValidarInscripcionExamenController;
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

// Validación pública por QR: Comprobante de Pago (CU7) y Comprobante de Inscripción a Examen (CU5).
Route::get('validar/comprobante/{uuid}', ValidarComprobanteController::class)
    ->whereUuid('uuid')->middleware('throttle:30,1')->name('validar.comprobante');

Route::get('validar/inscripcion-examen/{uuid}', ValidarInscripcionExamenController::class)
    ->whereUuid('uuid')->middleware('throttle:30,1')->name('validar.inscripcion-examen');
