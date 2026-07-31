<?php

namespace App\Helpers;

class WhatsAppHelper
{
    public static function normalizarTelefono(string $telefono): string
    {
        return preg_replace('/\D+/', '', $telefono) ?? '';
    }

    public static function mensajeTexto(array $message): ?string
    {
        return ($message['type'] ?? null) === 'text' ? data_get($message, 'text.body') : null;
    }
}
