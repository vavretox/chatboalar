<?php
namespace App\Services;
use App\Models\Cliente;
use App\Models\Producto;
class AiToolService {
 public function __construct(private CatalogoService $catalogo,private PedidoService $pedidos,private WhatsAppService $whatsapp){}
 public function execute(string $name,array $args,Cliente $cliente):array{return match($name){
  'buscar_productos'=>$this->buscar($args['consulta']??''),
  'consultar_stock'=>$this->stock($args['producto']??''),
  'agregar_al_carrito'=>$this->agregar($cliente,$args),
  'ver_carrito'=>['ok'=>true,'resumen'=>$this->pedidos->resumen($cliente)],
  'confirmar_pedido'=>$this->confirmar($cliente,$args),
  'consultar_pedido'=>$this->estado($cliente),
  'cancelar_pedido'=>$this->cancelar($cliente,$args),
  'enviar_imagen'=>$this->media($cliente,$args['producto']??'','image'),
  'enviar_documento'=>$this->media($cliente,$args['producto']??'','document'),
  default=>['ok'=>false,'error'=>'Herramienta desconocida']};}
 private function buscar(string $query):array{$items=$this->catalogo->disponibles()->filter(fn($p)=>blank($query)||str_contains(mb_strtolower($p->nombre.' '.$p->codigo.' '.$p->categoria),mb_strtolower($query)))->take(10)->map(fn($p)=>['id'=>$p->id,'codigo'=>$p->codigo,'nombre'=>$p->nombre,'descripcion'=>$p->descripcion,'precio'=>(float)$p->precio,'stock'=>$p->stock,'categoria'=>$p->categoria,'tiene_imagen'=>filled($p->imagen_url),'tiene_documento'=>filled($p->document_path)])->values()->all();return ['ok'=>true,'productos'=>$items];}
 private function product(string $text):?Producto{return $this->catalogo->buscar($text);}
 private function stock(string $text):array{$p=$this->product($text);return $p?['ok'=>true,'producto'=>$p->nombre,'stock'=>$p->stock,'precio'=>(float)$p->precio]:['ok'=>false,'error'=>'Producto no encontrado'];}
 private function agregar(Cliente $c,array $a):array{$p=$this->product($a['producto']??'');if(!$p)return ['ok'=>false,'error'=>'Producto no encontrado'];try{$this->pedidos->agregar($c,$p,(int)($a['cantidad']??1));return ['ok'=>true,'resumen'=>$this->pedidos->resumen($c)];}catch(\DomainException $e){return ['ok'=>false,'error'=>$e->getMessage()];}}
 private function confirmar(Cliente $c,array $a):array{if(($a['confirmacion_explicita']??false)!==true)return ['ok'=>false,'error'=>'Se requiere confirmación explícita del cliente'];try{$p=$this->pedidos->confirmar($c);return ['ok'=>true,'numero'=>$p->numero_pedido,'total'=>(float)$p->total,'estado'=>$p->estado];}catch(\DomainException $e){return ['ok'=>false,'error'=>$e->getMessage()];}}
 private function estado(Cliente $c):array{$p=$this->pedidos->ultimoPedido($c);return $p?['ok'=>true,'numero'=>$p->numero_pedido,'estado'=>$p->estado,'total'=>(float)$p->total]:['ok'=>false,'error'=>'No hay pedidos'];}
 private function cancelar(Cliente $c,array $a):array{if(($a['confirmacion_explicita']??false)!==true)return ['ok'=>false,'error'=>'Se requiere confirmación explícita'];try{$p=$this->pedidos->cancelar($c);return ['ok'=>true,'numero'=>$p->numero_pedido,'estado'=>$p->estado];}catch(\Throwable $e){return ['ok'=>false,'error'=>$e->getMessage()];}}
 private function media(Cliente $c,string $text,string $type):array{$p=$this->product($text);if(!$p)return ['ok'=>false,'error'=>'Producto no encontrado'];if($type==='image'){if(blank($p->imagen_url))return ['ok'=>false,'error'=>'El producto no tiene imagen'];$url=$this->publicUrl($p->imagen_url);$this->whatsapp->sendImage($c->telefono,$url,$p->nombre);return ['ok'=>true,'enviado'=>'imagen','producto'=>$p->nombre];}if(blank($p->document_path))return ['ok'=>false,'error'=>'El producto no tiene PDF'];$this->whatsapp->sendDocument($c->telefono,$this->publicUrl($p->document_path),$p->document_name?:$p->nombre.'.pdf');return ['ok'=>true,'enviado'=>'documento','producto'=>$p->nombre];}
 private function publicUrl(string $path):string{return str_starts_with($path,'http')?$path:asset('storage/'.$path);}
}
