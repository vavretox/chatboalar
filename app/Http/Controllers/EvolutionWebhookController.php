<?php
namespace App\Http\Controllers;
use App\Helpers\WhatsAppHelper;
use App\Jobs\ProcessWhatsAppMessage;
use App\Models\Cliente;
use App\Models\Conversacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class EvolutionWebhookController extends Controller {
 public function __invoke(Request $request):JsonResponse{
  $secret=(string)config('services.evolution.webhook_secret');if($secret!==''&&!hash_equals($secret,(string)$request->header('X-Webhook-Secret')))abort(401,'Webhook no autorizado.');
  if(!in_array(mb_strtolower((string)$request->input('event')),['messages.upsert','messages_upsert'],true))return response()->json(['status'=>'ignored']);
  $data=$request->input('data',[]);if(data_get($data,'key.fromMe',false))return response()->json(['status'=>'ignored']);
  $remote=(string)data_get($data,'key.remoteJid','');if(str_ends_with($remote,'@g.us'))return response()->json(['status'=>'ignored']);
  $phone=WhatsAppHelper::normalizarTelefono(str_replace(['@s.whatsapp.net','@lid'],'',$remote));
  $text=data_get($data,'message.conversation')??data_get($data,'message.extendedTextMessage.text')??data_get($data,'message.imageMessage.caption')??data_get($data,'message.documentMessage.caption');
  if($phone===''||blank($text))return response()->json(['status'=>'ignored']);
  $cliente=Cliente::firstOrCreate(['telefono'=>$phone],['nombre'=>data_get($data,'pushName',"Cliente {$phone}"),'whatsapp_id'=>$remote]);
  Conversacion::create(['cliente_id'=>$cliente->id,'tipo'=>'entrante','mensaje'=>$text,'metadata'=>['provider'=>'evolution','message_id'=>data_get($data,'key.id')]]);
  ProcessWhatsAppMessage::dispatch($cliente->id,$text)->onQueue('whatsapp');return response()->json(['status'=>'accepted']);
 }
}
