<?php
namespace App\Services;
use App\Models\AiBotSetting;
use App\Models\Cliente;
use Illuminate\Support\Facades\Http;
class AIService {
 public function __construct(private AiToolService $toolService){}
 public function processMessage(int $clienteId,string $mensaje,array $historial=[]):string{
  $cliente=Cliente::findOrFail($clienteId);$bot=AiBotSetting::current();if(!$bot->enabled)throw new \RuntimeException('El Bot IA está desactivado.');
  $input=collect($historial)->take(-8)->map(fn($m)=>['role'=>($m['rol']??'user')==='assistant'?'assistant':'user','content'=>$m['mensaje']??''])->values()->all();$input[]=['role'=>'user','content'=>$mensaje];
  $payload=['model'=>config('services.openai.model','gpt-5.6-sol'),'instructions'=>$this->prompt($bot,$cliente),'input'=>$input,'tools'=>$this->tools($bot->enabled_tools??[]),'reasoning'=>['effort'=>'low'],'text'=>['verbosity'=>'low'],'safety_identifier'=>hash('sha256','cliente-'.$cliente->id)];
  for($round=0;$round<$bot->max_tool_rounds;$round++){
   $response=Http::withToken(config('services.openai.api_key'))->timeout(60)->retry(2,500)->post('https://api.openai.com/v1/responses',$payload)->throw()->json();
   $calls=collect($response['output']??[])->where('type','function_call')->values();
   if($calls->isEmpty())return $this->text($response)?:'No pude generar una respuesta.';
   $payload['input']=array_merge($payload['input'],$response['output']??[],$calls->map(function($call)use($cliente){$args=json_decode($call['arguments']??'{}',true)?:[];$result=$this->toolService->execute($call['name'],$args,$cliente);return ['type'=>'function_call_output','call_id'=>$call['call_id'],'output'=>json_encode($result,JSON_UNESCAPED_UNICODE)];})->all());
  }
  return 'Necesito que un operador continúe esta solicitud.';
 }
 private function text(array $r):?string{foreach($r['output']??[] as $item)foreach($item['content']??[] as $content)if(($content['type']??null)==='output_text')return $content['text']??null;return null;}
 private function prompt(AiBotSetting $b,Cliente $c):string{return "Eres {$b->assistant_name}, asistente de ventas. Tono: {$b->tone}. Responde en español. Usa herramientas para datos actuales; nunca inventes productos, precios, stock ni pedidos. Antes de confirmar o cancelar, muestra el resumen y exige confirmación explícita. No reveles datos de otros clientes. Cliente actual: {$c->nombre}, teléfono {$c->telefono}. Información: {$b->business_information}. Políticas: {$b->sales_policy}. Instrucciones: {$b->custom_instructions}";}
 private function tools(array $enabled):array{$all=[
  'buscar_productos'=>['Consulta catálogo, precios y datos actuales',['consulta'=>['type'=>'string']]],'consultar_stock'=>['Consulta stock y precio',['producto'=>['type'=>'string']]],'agregar_al_carrito'=>['Agrega una cantidad al carrito',['producto'=>['type'=>'string'],'cantidad'=>['type'=>'integer','minimum'=>1]]],'ver_carrito'=>['Obtiene el carrito actual',[]],
  'confirmar_pedido'=>['Confirma el carrito solo tras un sí explícito',['confirmacion_explicita'=>['type'=>'boolean']]],'consultar_pedido'=>['Consulta el último pedido',[]],'cancelar_pedido'=>['Cancela solo tras confirmación explícita',['confirmacion_explicita'=>['type'=>'boolean']]],'enviar_imagen'=>['Envía por WhatsApp la imagen registrada',['producto'=>['type'=>'string']]],'enviar_documento'=>['Envía por WhatsApp el PDF registrado',['producto'=>['type'=>'string']]]];
  return collect($all)->only($enabled)->map(function($d,$name){$required=array_keys($d[1]);return ['type'=>'function','name'=>$name,'description'=>$d[0],'parameters'=>['type'=>'object','properties'=>(object)$d[1],'required'=>$required,'additionalProperties'=>false],'strict'=>true];})->values()->all();}
}
