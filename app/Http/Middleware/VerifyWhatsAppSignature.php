<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class VerifyWhatsAppSignature {
    public function handle(Request $request,Closure $next): Response {
        $secret=(string)config('services.whatsapp.app_secret'); $provided=(string)$request->header('X-Hub-Signature-256'); $expected='sha256='.hash_hmac('sha256',$request->getContent(),$secret);
        if ($secret === '' && app()->environment('local', 'testing')) { return $next($request); }
        abort_unless($secret!==''&&hash_equals($expected,$provided),401,'Firma de WhatsApp inválida.'); return $next($request);
    }
}
