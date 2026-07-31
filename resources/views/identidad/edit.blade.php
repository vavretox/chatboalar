@extends('layouts.admin')
@section('title','Identidad')
@section('content')
<div class="head"><div><h1>Identidad del sistema</h1><p>Personaliza el nombre, imagen y colores que las personas ven en el panel.</p></div></div>
<form class="card" method="POST" enctype="multipart/form-data" action="{{ route('identidad.update') }}">
@csrf @method('PUT')
<div class="grid">
@foreach(['name'=>'Nombre del sistema','tagline'=>'Texto bajo el nombre','dashboard_title'=>'Título principal','dashboard_subtitle'=>'Descripción principal'] as $field=>$label)
<div class="{{ str_contains($field,'dashboard') ? 'full' : '' }}"><label>{{ $label }}</label><input name="{{ $field }}" value="{{ old($field,$identidad->$field) }}" required>@error($field)<div class="error">{{ $message }}</div>@enderror</div>
@endforeach
<div class="full"><label>Imagen o logotipo de la empresa</label><div style="display:flex;gap:18px;align-items:center;flex-wrap:wrap">
@if($identidad->logo_url)<img src="{{ asset('storage/'.$identidad->logo_url) }}" alt="Imagen de la empresa" style="width:150px;height:150px;object-fit:contain;border:0;background:transparent;padding:0">@else<div style="width:150px;height:150px;background:#eef3f0;display:grid;place-items:center;color:#718078">Sin imagen</div>@endif
<div style="flex:1;min-width:230px"><input type="file" name="logo_image" accept="image/jpeg,image/png,image/webp,image/gif" id="logo_image"><div style="font-size:11px;color:var(--muted);margin-top:6px">JPG, PNG, WEBP o GIF. Tamaño máximo: 4 MB.</div>@error('logo_image')<div class="error">{{ $message }}</div>@enderror
@if($identidad->logo_url)<div class="check" style="margin-top:10px"><input type="checkbox" name="remove_logo" id="remove_logo" value="1"><label for="remove_logo">Quitar la imagen actual</label></div>@endif</div></div></div>
<div><label>Color principal</label><div class="colors"><input type="color" name="primary_color" value="{{ old('primary_color',$identidad->primary_color) }}"><span>Botones y elementos destacados</span></div></div>
<div><label>Color del menú</label><div class="colors"><input type="color" name="sidebar_color" value="{{ old('sidebar_color',$identidad->sidebar_color) }}"><span>Barra lateral y cabeceras</span></div></div>
</div><div class="actions"><a class="btn light" href="{{ url('/') }}">Cancelar</a><button class="btn">Guardar identidad</button></div></form>
<script>document.getElementById('logo_image').addEventListener('change',function(){var f=this.files[0];if(!f)return;var img=document.querySelector('img[alt="Imagen de la empresa"]');if(img)img.src=URL.createObjectURL(f);});</script>
@endsection
