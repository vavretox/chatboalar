<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $identidad->name }} · Panel de control</title>
    <style>
        :root{--ink:#14201b;--muted:#6c7973;--line:#e5ebe7;--green:{{ $identidad->primary_color }};--green2:{{ $identidad->primary_color }};--soft:#edf9f3;--orange:#ff9f43;--bg:#f5f7f6;--white:#fff;--shadow:0 10px 30px rgba(20,32,27,.07)}
        *{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--ink);font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif}.layout{min-height:100vh;display:grid;grid-template-columns:250px 1fr}.sidebar{background:#10271f;color:#fff;padding:26px 18px;position:sticky;top:0;height:100vh}.brand{display:flex;align-items:center;gap:11px;font-weight:800;font-size:20px;padding:0 10px 28px}.brand-mark{display:grid;place-items:center;width:38px;height:38px;border-radius:12px;background:var(--green);font-size:20px}.brand small{display:block;color:#91a69d;font-size:10px;letter-spacing:1.5px;margin-top:2px}.nav-label{color:#6f8b80;font-size:10px;letter-spacing:1.4px;font-weight:800;margin:22px 12px 8px}.nav a{display:flex;gap:12px;align-items:center;color:#bcd0c8;text-decoration:none;padding:12px;border-radius:10px;font-size:14px;margin:3px 0}.nav a.active,.nav a:hover{background:#1a3b30;color:#fff}.nav i{font-style:normal;width:20px;text-align:center}.side-status{position:absolute;left:18px;right:18px;bottom:24px;padding:14px;background:#17372c;border:1px solid #255143;border-radius:12px;font-size:12px;color:#bcd0c8}.dot{display:inline-block;width:8px;height:8px;border-radius:50%;background:#31d58b;margin-right:7px;box-shadow:0 0 0 4px rgba(49,213,139,.12)}main{padding:28px 34px 50px;min-width:0}.top{display:flex;justify-content:space-between;align-items:center;margin-bottom:26px}.top h1{font-size:25px;margin:0 0 5px}.top p{margin:0;color:var(--muted);font-size:14px}.button{border:0;border-radius:10px;padding:11px 16px;background:var(--green);color:#fff;font-weight:700;text-decoration:none;font-size:13px}.metrics{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}.card{background:#fff;border:1px solid var(--line);border-radius:15px;box-shadow:var(--shadow)}.metric{padding:20px}.metric-head{display:flex;justify-content:space-between;color:var(--muted);font-size:12px;font-weight:700}.metric-icon{display:grid;place-items:center;width:34px;height:34px;border-radius:10px;background:var(--soft);font-size:17px}.metric strong{display:block;font-size:28px;margin-top:12px}.metric small{color:#8b9892}.grid{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(280px,.75fr);gap:18px;margin-top:18px}.section{padding:21px}.section-title{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px}.section-title h2{font-size:16px;margin:0}.section-title span{font-size:12px;color:var(--muted)}table{border-collapse:collapse;width:100%;font-size:13px}th{text-align:left;color:#7d8a84;font-size:11px;text-transform:uppercase;letter-spacing:.5px;padding:0 10px 12px}td{border-top:1px solid var(--line);padding:13px 10px}.product{display:flex;align-items:center;gap:10px}.product-icon{width:37px;height:37px;border-radius:10px;background:var(--soft);display:grid;place-items:center}.product b{display:block}.product small{color:var(--muted)}.badge{display:inline-block;padding:5px 9px;border-radius:20px;background:var(--soft);color:var(--green2);font-size:11px;font-weight:700}.stock-low{background:#fff1e4;color:#b7600b}.setup{display:flex;gap:12px;padding:14px 0;border-bottom:1px solid var(--line)}.setup:last-child{border:0}.setup-icon{width:39px;height:39px;flex:0 0 39px;border-radius:11px;display:grid;place-items:center;background:#f1f4f2}.setup b{font-size:13px}.setup p{font-size:11px;color:var(--muted);margin:4px 0 0;line-height:1.5}.state{margin-left:auto;font-size:10px;font-weight:800;padding-top:4px;color:#c27a18}.state.ok{color:var(--green)}.feature-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}.feature{border:1px solid var(--line);border-radius:11px;padding:12px;font-size:12px}.feature span{color:var(--green);font-weight:900;margin-right:6px}.empty{padding:25px;text-align:center;color:var(--muted);font-size:13px}.footer{color:#8a9791;font-size:11px;text-align:center;margin-top:24px}@media(max-width:1000px){.metrics{grid-template-columns:repeat(2,1fr)}.grid{grid-template-columns:1fr}.feature-grid{grid-template-columns:repeat(2,1fr)}}@media(max-width:700px){.layout{display:block}.sidebar{height:auto;position:static}.nav,.nav-label,.side-status{display:none}main{padding:20px 14px}.metrics{grid-template-columns:1fr 1fr;gap:10px}.metric{padding:15px}.top{align-items:flex-start}.top .button{display:none}.feature-grid{grid-template-columns:1fr}table th:nth-child(2),table td:nth-child(2){display:none}}
        .brand:has(.company-logo){padding-left:0;gap:14px}.company-logo{width:68px!important;height:68px!important;flex:0 0 68px;object-fit:contain!important;padding:0!important;background:transparent!important;border:0!important;border-radius:0!important;box-shadow:none!important}
    </style>
</head>
<body>
<div class="layout">
    <aside class="sidebar" style="background:{{ $identidad->sidebar_color }}">
        <div class="brand">@if($identidad->logo_url)<img src="{{ asset('storage/'.$identidad->logo_url) }}" class="company-logo" alt="Logotipo de {{ $identidad->name }}">@else<span class="brand-mark">✦</span>@endif<div>{{ $identidad->name }}<small>{{ $identidad->tagline }}</small></div></div>
        <div class="nav-label">MENÚ PRINCIPAL</div>
        <nav class="nav">
            <a class="active" href="#resumen"><i>▦</i> Resumen</a><a href="{{ route('productos.index') }}"><i>◫</i> Catálogo</a><a href="{{ route('pedidos.index') }}"><i>◎</i> Pedidos</a>@if(auth()->user()->role === 'administrador')<a href="{{ route('usuarios.index') }}"><i>♙</i> Usuarios</a>@endif
        </nav>
        <div class="nav-label">CONFIGURACIÓN</div>
        @if(auth()->user()->role === 'administrador')<nav class="nav"><a href="{{ route('integraciones.index') }}"><i>⚙</i> Integraciones</a><a href="{{ route('bot-ia.edit') }}"><i>◆</i> Bot IA</a><a href="{{ route('identidad.edit') }}"><i>✦</i> Identidad</a><a href="{{ url('/api/test') }}" target="_blank"><i>↗</i> Probar API</a></nav>@endif
        <div class="side-status"><span class="dot"></span><strong>Sistema activo</strong><br><span style="margin-left:18px">Laravel {{ app()->version() }}</span></div>
    </aside>
    <main>
        <header class="top" id="resumen"><div><h1>{{ $identidad->dashboard_title }}</h1><p>{{ $identidad->dashboard_subtitle }}</p><small style="color:var(--muted)">{{ auth()->user()->name }} · {{ ucfirst(auth()->user()->role) }}</small></div><form method="POST" action="{{ route('logout') }}">@csrf<button class="button" type="submit">Cerrar sesión</button></form></header>
        <section class="metrics">
            <article class="card metric"><div class="metric-head"><span>PRODUCTOS ACTIVOS</span><span class="metric-icon">◫</span></div><strong>{{ $metricas['productos'] }}</strong><small>{{ $metricas['stock'] }} unidades en inventario</small></article>
            <article class="card metric"><div class="metric-head"><span>CLIENTES</span><span class="metric-icon">♙</span></div><strong>{{ $metricas['clientes'] }}</strong><small>Contactos registrados</small></article>
            <article class="card metric"><div class="metric-head"><span>CONVERSACIONES</span><span class="metric-icon">◌</span></div><strong>{{ $metricas['mensajes'] }}</strong><small>Mensajes almacenados</small></article>
            <article class="card metric"><div class="metric-head"><span>PEDIDOS</span><span class="metric-icon">✓</span></div><strong>{{ $pedidos->count() }}</strong><small>Pedidos recientes</small></article>
        </section>
        <div class="grid">
            <section class="card section" id="catalogo"><div class="section-title"><h2>Catálogo de productos</h2><span><a href="{{ route('productos.index') }}" style="color:var(--green2);text-decoration:none;font-weight:700">Ver catálogo →</a></span></div>
                <table><thead><tr><th>Producto</th><th>Categoría</th><th>Precio</th><th>Stock</th></tr></thead><tbody>
                @forelse($productos as $producto)<tr><td><div class="product"><span class="product-icon">◇</span><div><b>{{ $producto->nombre }}</b><small>{{ $producto->codigo }}</small></div></div></td><td><span class="badge">{{ ucfirst($producto->categoria) }}</span></td><td>${{ number_format((float)$producto->precio, 2, ',', '.') }}</td><td><span class="badge {{ $producto->stock < 20 ? 'stock-low' : '' }}">{{ $producto->stock }} und.</span></td></tr>@empty<tr><td colspan="4" class="empty">No hay productos cargados.</td></tr>@endforelse
                </tbody></table>
            </section>
            <aside class="card section" id="integraciones"><div class="section-title"><h2>Integraciones</h2><span><a href="{{ route('integraciones.index') }}" style="color:var(--green2);text-decoration:none;font-weight:700">Ver integraciones →</a></span></div>
                <div class="setup"><span class="setup-icon">◉</span><div><b>WhatsApp Cloud API</b><p>Webhook: /api/whatsapp/webhook</p></div><span class="state {{ $whatsappConfigurado ? 'ok' : '' }}">{{ $whatsappConfigurado ? 'LISTO' : 'PENDIENTE' }}</span></div>
                <div class="setup"><span class="setup-icon">◈</span><div><b>Evolution API</b><p>Instancia en EasyPanel</p></div><span class="state {{ $evolutionConfigurado ? 'ok' : '' }}">{{ $evolutionConfigurado ? 'ACTIVO' : 'PENDIENTE' }}</span></div>
                <div class="setup"><span class="setup-icon">✦</span><div><b>OpenAI</b><p>Generación de respuestas inteligentes</p></div><span class="state {{ $openaiConfigurado ? 'ok' : '' }}">{{ $openaiConfigurado ? 'LISTO' : 'PENDIENTE' }}</span></div>
                <div class="setup"><span class="setup-icon">▤</span><div><b>MySQL</b><p>Catálogo, clientes y conversaciones</p></div><span class="state ok">ACTIVO</span></div>
            </aside>
        </div>
        <div class="grid">
            <section class="card section" id="funciones"><div class="section-title"><h2>Características implementadas</h2><span>Backend</span></div><div class="feature-grid">
                @foreach(['Webhook de WhatsApp','Gestión de clientes','Catálogo de productos','Carrito de compras','Procesamiento de pedidos','Descuento de stock','Estado de pedidos','Cancelación de pedidos','Integración OpenAI','Historial de conversación','Colas de Laravel','Datos de ejemplo'] as $feature)<div class="feature"><span>✓</span>{{ $feature }}</div>@endforeach
            </div></section>
            <section class="card section" id="pedidos"><div class="section-title"><h2>Pedidos recientes</h2><span>Últimos 5</span></div>
                @forelse($pedidos as $pedido)<div class="setup"><span class="setup-icon">#</span><div><b>{{ $pedido->numero_pedido }}</b><p>{{ $pedido->cliente?->nombre }} · ${{ number_format((float)$pedido->total,2,',','.') }}</p></div><span class="state ok">{{ strtoupper($pedido->estado) }}</span></div>@empty<div class="empty">Los pedidos recibidos por el chatbot aparecerán aquí.</div>@endforelse
            </section>
        </div>
        <div class="footer">{{ $identidad->name }} · Entorno local · {{ now()->format('Y') }}</div>
    </main>
</div>
</body>
</html>
