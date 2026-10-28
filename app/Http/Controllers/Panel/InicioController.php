<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class InicioController extends Controller
{
    public function __invoke(Request $request): View
    {
        $accesos = collect(config('adminlte.menu'))
            ->filter(fn (array $item): bool => isset($item['text'], $item['route']) && $item['route'] !== 'panel.inicio')
            ->filter(fn (array $item): bool => ! isset($item['can']) || $request->user()->can($item['can']))
            ->take(8)
            ->map(fn (array $item): array => [
                'texto' => $item['text'],
                'ruta' => $item['route'],
                'icono' => $item['icon'] ?? 'fas fa-arrow-right',
            ]);

        return view('panel.inicio', [
            'accesos' => $accesos,
            'fecha' => now()->locale('es')->isoFormat('dddd D [de] MMMM'),
        ]);
    }
}
