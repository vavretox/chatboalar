@extends('layouts.admin') @section('title','Bot IA') @section('content')
<div class="head"><div><h1>Bot IA</h1><p>Define la identidad, conocimiento y acciones permitidas del asistente.</p></div></div>
<form class="card" method="POST" action="{{ route('bot-ia.update') }}">@csrf @method('PUT')<div class="grid">
<div><label>Nombre del asistente</label><input name="assistant_name" value="{{ old('assistant_name',$bot->assistant_name) }}" required></div><div><label>Tono de comunicación</label><input name="tone" value="{{ old('tone',$bot->tone) }}" required></div>
<div class="full"><label>Mensaje de bienvenida</label><textarea name="welcome_message" placeholder="¡Hola! ¿En qué puedo ayudarte?">{{ old('welcome_message',$bot->welcome_message) }}</textarea></div>
<div class="full"><label>Información del negocio</label><textarea name="business_information" placeholder="Horarios, zonas de entrega, métodos de pago...">{{ old('business_information',$bot->business_information) }}</textarea></div>
<div class="full"><label>Políticas de venta</label><textarea name="sales_policy" placeholder="Condiciones de pedidos, entregas y devoluciones...">{{ old('sales_policy',$bot->sales_policy) }}</textarea></div>
<div class="full"><label>Instrucciones adicionales del prompt</label><textarea name="custom_instructions" style="min-height:150px" placeholder="Reglas particulares que debe seguir el asistente...">{{ old('custom_instructions',$bot->custom_instructions) }}</textarea></div>
<div class="full"><label>Acciones permitidas</label><div class="grid" style="gap:10px">@foreach($tools as $key=>$label)<div class="check"><input type="checkbox" id="{{ $key }}" name="enabled_tools[]" value="{{ $key }}" @checked(in_array($key,old('enabled_tools',$bot->enabled_tools??[])))><label for="{{ $key }}">{{ $label }}</label></div>@endforeach</div></div>
<div><label>Máximo de acciones por mensaje</label><input type="number" name="max_tool_rounds" min="1" max="8" value="{{ old('max_tool_rounds',$bot->max_tool_rounds) }}"></div><div class="check"><input type="hidden" name="enabled" value="0"><input type="checkbox" id="bot_enabled" name="enabled" value="1" @checked(old('enabled',$bot->enabled))><label for="bot_enabled">Bot IA activo</label></div>
</div><div class="actions"><a class="btn light" href="{{ url('/') }}">Cancelar</a><button class="btn">Guardar Bot IA</button></div></form>
@endsection
