<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FORTALEZA — Ingresar</title>
    <script src="{{ asset('theme.js') }}"></script>
    @vite(['resources/css/app.css'])
</head>
<body class="login-page">
    <section class="login-story">
        <a href="{{ url('/') }}" class="brand"><img src="{{ asset('sello-moriah-fortaleza.svg') }}" class="brand-logo" width="56" height="56" alt="Moriah Fortaleza"><span>FORTALEZA<small>GESTIÓN FINANCIERA</small></span></a>
        <div><span class="section-tag">ORDEN QUE CONSTRUYE</span><h2>Una comunidad.<br>Un futuro<br>más sólido.</h2><p>Un espacio para administrar los recursos de FORTALEZA con claridad y confianza.</p></div>
        <footer>FORTALEZA · Administración y finanzas</footer>
    </section>
    <main class="login-form-panel"><div class="login-card">
        <p class="eyebrow">BIENVENIDO A FORTALEZA</p><h1>Ingresa a tu cuenta</h1><p class="text-slate-400 text-sm mt-3 mb-8">Usa tus credenciales para continuar.</p>
        @if ($errors->any())
            <div role="alert" class="bg-red-900/50 border border-red-700 text-red-200 text-sm rounded p-3 mb-5">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ url('/login') }}" class="space-y-5">
            @csrf
            <div><label for="cc_user">Usuario</label><input type="text" name="cc_user" id="cc_user" value="{{ old('cc_user') }}" required autofocus autocomplete="username" placeholder="Tu nombre de usuario"></div>
            <div><label for="ct_clave">Clave</label><input type="password" name="ct_clave" id="ct_clave" required autocomplete="current-password" placeholder="Tu contraseña"></div>
            <button type="submit">Ingresar <span aria-hidden="true" class="ml-3">→</span></button>
        </form>
        <p class="text-xs text-slate-400 mt-8 text-center">Acceso exclusivo para usuarios autorizados.</p>
    </div><div class="login-theme-toggle">@include('layouts.partials.theme-toggle')</div></main>
</body>
</html>
