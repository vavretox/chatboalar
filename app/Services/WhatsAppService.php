<?php
namespace App\Services;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
class WhatsAppService {
 public function sendTextMessage(string $to,string $text):Response{return config('services.evolution.enabled')?$this->evolutionText($to,$text):$this->cloudText($to,$text);}
 public function sendImage(string $to,string $url,?string $caption=null):Response{return config('services.evolution.enabled')?$this->evolutionMedia($to,$url,'image',$caption,'imagen.jpg'):$this->cloudMedia($to,'image',['link'=>$url,'caption'=>$caption]);}
 public function sendDocument(string $to,string $url,string $filename):Response{return config('services.evolution.enabled')?$this->evolutionMedia($to,$url,'document',null,$filename):$this->cloudMedia($to,'document',['link'=>$url,'filename'=>$filename]);}
 private function cloudText(string $to,string $text):Response{$v=config('services.whatsapp.api_version');$id=config('services.whatsapp.phone_number_id');return Http::withToken(config('services.whatsapp.access_token'))->timeout(20)->retry(3,500)->post("https://graph.facebook.com/{$v}/{$id}/messages",['messaging_product'=>'whatsapp','recipient_type'=>'individual','to'=>$to,'type'=>'text','text'=>['preview_url'=>false,'body'=>$text]])->throw();}
 private function cloudMedia(string $to,string $type,array $media):Response{$v=config('services.whatsapp.api_version');$id=config('services.whatsapp.phone_number_id');return Http::withToken(config('services.whatsapp.access_token'))->timeout(20)->retry(3,500)->post("https://graph.facebook.com/{$v}/{$id}/messages",['messaging_product'=>'whatsapp','to'=>$to,'type'=>$type,$type=>$media])->throw();}
 private function evolutionText(string $to,string $text):Response{return $this->evolution()->post($this->evolutionUrl('message/sendText'),['number'=>$to,'textMessage'=>['text'=>$text]])->throw();}
 private function evolutionMedia(string $to,string $url,string $type,?string $caption,string $filename):Response{$file=Http::timeout(20)->get($url)->throw()->body();return $this->evolution()->attach('media',$file,$filename)->post($this->evolutionUrl('message/sendMedia'),['number'=>$to,'mediatype'=>$type,'caption'=>$caption??'','fileName'=>$filename])->throw();}
 private function evolution(){return Http::withHeaders(['apikey'=>config('services.evolution.api_key')])->timeout(30)->retry(3,500);}
 private function evolutionUrl(string $path):string{return rtrim((string)config('services.evolution.base_url'),'/').'/'.$path.'/'.rawurlencode((string)config('services.evolution.instance'));}
}
