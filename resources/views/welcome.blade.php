<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FORTALEZA — Gestión financiera</title>
    <script src="{{ asset('theme.js') }}"></script>
    @vite(['resources/css/app.css'])
</head>
<body class="app-shell">
    <header class="welcome-header max-w-6xl mx-auto flex items-center justify-between px-6">
        <a href="{{ url('/') }}" class="brand"><img src="{{ asset('sello-moriah-fortaleza.svg') }}" class="brand-logo" width="56" height="56" alt="Moriah Fortaleza"><span>FORTALEZA<small>GESTIÓN FINANCIERA</small></span></a>
        <a href="{{ auth()->check() ? url('/home') : url('/login') }}" class="text-sm text-emerald-200">{{ auth()->check() ? 'Ir al panel' : 'Ingresar' }} <span aria-hidden="true">↗</span></a>
    </header>
    <main class="max-w-6xl mx-auto px-6 py-12 sm:py-20">
        <section class="welcome-panel">
            <div class="relative z-10"><span class="section-tag">ORDEN QUE CONSTRUYE</span><h1 class="text-4xl sm:text-6xl font-semibold tracking-tight mt-8 leading-tight">Una comunidad.<br>Un futuro más sólido.</h1><p>Administra los recursos de FORTALEZA con claridad.<br class="hidden sm:block"> Ingresos, egresos y cuentas, en un mismo espacio.</p><a href="{{ auth()->check() ? url('/home') : url('/login') }}" class="primary-link">{{ auth()->check() ? 'Abrir mi panel' : 'Ingresar a mi cuenta' }} <span aria-hidden="true">↗</span></a></div>
        </section>
        <div class="quick-grid mt-6">
            @foreach (['Ingresos y recibos', 'Control de egresos', 'Estado de cuenta', 'Gestión de socios'] as $nombre)
                <div class="quick-card"><span class="eyebrow">0{{ $loop->iteration }}</span><h2 class="text-sm mt-3">{{ $nombre }}</h2></div>
            @endforeach
        </div>
    </main>
    <footer class="welcome-footer max-w-6xl mx-auto px-6 py-8 text-xs text-slate-400"><span>FORTALEZA · Administración y finanzas</span>@include('layouts.partials.theme-toggle')</footer>
</body>
</html>
