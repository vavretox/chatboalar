<?php
namespace App\Http\Controllers;
use App\Models\AiBotSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class AiBotSettingController extends Controller {
 public function edit():View{return view('bot-ia.edit',['bot'=>AiBotSetting::current(),'tools'=>$this->tools()]);}
 public function update(Request $request):RedirectResponse{$data=$request->validate(['assistant_name'=>'required|string|max:100','tone'=>'required|string|max:255','welcome_message'=>'nullable|string|max:2000','business_information'=>'nullable|string|max:10000','sales_policy'=>'nullable|string|max:10000','custom_instructions'=>'nullable|string|max:20000','enabled_tools'=>'nullable|array','enabled_tools.*'=>['string','in:'.implode(',',array_keys($this->tools()))],'enabled'=>'nullable|boolean','max_tool_rounds'=>'required|integer|min:1|max:8']);$data['enabled']=$request->boolean('enabled');$data['enabled_tools']=$data['enabled_tools']??[];AiBotSetting::current()->update($data);return back()->with('success','Configuración del Bot IA guardada.');}
 private function tools():array{return ['buscar_productos'=>'Buscar productos y precios','consultar_stock'=>'Consultar inventario','agregar_al_carrito'=>'Agregar al carrito','ver_carrito'=>'Ver carrito','confirmar_pedido'=>'Confirmar pedido','consultar_pedido'=>'Consultar estado del pedido','cancelar_pedido'=>'Cancelar pedido','enviar_imagen'=>'Enviar imagen del producto','enviar_documento'=>'Enviar PDF del producto'];}
}
