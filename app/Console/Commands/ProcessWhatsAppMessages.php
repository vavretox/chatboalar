<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ProcessWhatsAppMessages extends Command
{
    protected $signature = 'whatsapp:process {--stop-when-empty : Terminar cuando la cola quede vacía}';
    protected $description = 'Procesa la cola de mensajes entrantes de WhatsApp';

    public function handle(): int
    {
        return Artisan::call('queue:work', [
            '--queue' => 'whatsapp,default', '--tries' => 3, '--timeout' => 90,
            '--stop-when-empty' => (bool) $this->option('stop-when-empty'),
        ], $this->output);
    }
}
