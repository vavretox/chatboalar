<?php

use App\Models\Cliente;
use App\Models\Conversacion;
use App\Models\Pedido;
use App\Models\Producto;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AiBotSettingController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware(['auth', 'active'])->group(function (): void {
Route::get('/', function () {
    $sqliteNoDisponible = config('database.default') === 'sqlite' && ! extension_loaded('pdo_sqlite');
    $productos = $sqliteNoDisponible ? new Collection : Producto::query()->orderBy('categoria')->orderBy('nombre')->get();
    $pedidos = $sqliteNoDisponible ? new Collection : Pedido::query()->with('cliente')->latest()->limit(5)->get();

    return view('dashboard', [
        'productos' => $productos,
        'pedidos' => $pedidos,
        'metricas' => [
            'productos' => $sqliteNoDisponible ? 0 : Producto::where('activo', true)->count(),
            'stock' => $sqliteNoDisponible ? 0 : Producto::sum('stock'),
            'clientes' => $sqliteNoDisponible ? 0 : Cliente::count(),
            'mensajes' => $sqliteNoDisponible ? 0 : Conversacion::count(),
        ],
        'whatsappConfigurado' => filled(config('services.whatsapp.access_token')),
        'openaiConfigurado' => filled(config('services.openai.api_key')),
        'evolutionConfigurado' => (bool) config('services.evolution.enabled'),
    ]);
})->name('dashboard');

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::resource('productos', ProductoController::class)->except('show');
Route::resource('pedidos', PedidoController::class)->only(['index','edit','update']);

Route::middleware('admin')->group(function (): void {
    Route::get('integraciones', [IntegrationController::class, 'index'])->name('integraciones.index');
    Route::put('integraciones/canal-whatsapp', [IntegrationController::class, 'channel'])->name('integraciones.channel');
    Route::put('integraciones/{provider}', [IntegrationController::class, 'update'])->name('integraciones.update');
    Route::post('integraciones/{provider}/probar', [IntegrationController::class, 'test'])->name('integraciones.test');
    Route::get('identidad', [SystemSettingController::class, 'edit'])->name('identidad.edit');
    Route::put('identidad', [SystemSettingController::class, 'update'])->name('identidad.update');
    Route::resource('usuarios', UserController::class)->except('show');
    Route::get('bot-ia', [AiBotSettingController::class, 'edit'])->name('bot-ia.edit');
    Route::put('bot-ia', [AiBotSettingController::class, 'update'])->name('bot-ia.update');
});
});
