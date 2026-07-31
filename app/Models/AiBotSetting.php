<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AiBotSetting extends Model {protected $fillable=['assistant_name','tone','welcome_message','business_information','sales_policy','custom_instructions','enabled_tools','enabled','max_tool_rounds'];protected function casts():array{return ['enabled_tools'=>'array','enabled'=>'boolean','max_tool_rounds'=>'integer'];}public static function current():self{return self::firstOrCreate([],['enabled_tools'=>['buscar_productos','consultar_stock','agregar_al_carrito','ver_carrito','confirmar_pedido','consultar_pedido','cancelar_pedido','enviar_imagen','enviar_documento']]);}}
