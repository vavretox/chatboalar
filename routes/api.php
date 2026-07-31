<?php

use App\Http\Controllers\WhatsAppWebhookController;
use App\Http\Middleware\VerifyWhatsAppSignature;
use App\Http\Controllers\EvolutionWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('whatsapp')->group(function (): void {
    Route::get('/webhook', [WhatsAppWebhookController::class, 'verify'])->name('whatsapp.verify');
    Route::post('/webhook', [WhatsAppWebhookController::class, 'webhook'])->middleware(VerifyWhatsAppSignature::class)->name('whatsapp.webhook');
});

Route::get('/test', fn () => response()->json([
    'message' => 'Chatbot funcionando correctamente',
    'status' => 'success',
]));

Route::post('/evolution/webhook', EvolutionWebhookController::class)->name('evolution.webhook');
