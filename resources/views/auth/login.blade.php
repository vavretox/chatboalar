<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión · {{ $identidad->name }}</title>
    <style>
        :root{--primary:{{ $identidad->primary_color }};--dark:{{ $identidad->sidebar_color }};--muted:#68776f;--line:#dce6e1}*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:22px;background:linear-gradient(145deg,var(--dark),#173f31);font-family:Inter,system-ui,-apple-system,"Segoe UI",sans-serif;color:#17231e}.login{width:min(430px,100%);background:#fff;border-radius:20px;padding:34px;box-shadow:0 25px 70px rgba(0,0,0,.25)}.brand{text-align:center;margin-bottom:27px}.logo{width:88px;height:88px;object-fit:contain;margin-bottom:9px}.mark{display:grid;place-items:center;width:62px;height:62px;margin:0 auto 12px;border-radius:18px;background:var(--primary);color:#fff;font-size:29px}.brand h1{font-size:25px;margin:0 0 5px}.brand p{font-size:13px;color:var(--muted);margin:0}label{display:block;font-size:12px;font-weight:800;margin:16px 0 7px}input[type=email],input[type=password]{width:100%;border:1px solid #c9d6d0;border-radius:10px;padding:12px 13px;font:inherit;font-size:14px;outline:none}input:focus{border-color:var(--primary);box-shadow:0 0 0 3px color-mix(in srgb,var(--primary) 15%,transparent)}.remember{display:flex;align-items:center;gap:8px;margin:15px 0 20px;color:var(--muted);font-size:12px}.remember input{width:auto}.button{width:100%;border:0;border-radius:10px;padding:12px;background:var(--primary);color:#fff;font:inherit;font-weight:800;cursor:pointer}.error{border-radius:9px;padding:10px 12px;background:#fff0ee;color:#a9362c;font-size:12px;margin-bottom:10px}@media(max-width:480px){.login{padding:27px 20px}}
    </style>
</head>
<body>
<main class="login">
    <div class="brand">
        @if($identidad->logo_url)<img class="logo" src="{{ asset('storage/'.$identidad->logo_url) }}" alt="{{ $identidad->name }}">@else<div class="mark">✦</div>@endif
        <h1>{{ $identidad->name }}</h1>
        <p>Ingresa para administrar el chatbot</p>
    </div>
    @if($errors->any())<div class="error">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <label for="email">Correo electrónico</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        <label for="password">Contraseña</label>
        <input id="password" type="password" name="password" required autocomplete="current-password">
        <label class="remember"><input type="checkbox" name="remember" value="1"> Mantener mi sesión iniciada</label>
        <button class="button" type="submit">Iniciar sesión</button>
    </form>
</main>
</body>
</html>
