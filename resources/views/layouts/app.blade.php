<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'FORTALEZA' }}</title>
    <script src="{{ asset('theme.js') }}"></script>
    @vite(['resources/css/app.css'])
    @livewireStyles
</head>
<body class="app-shell" x-data="{ menuAbierto: false }" @keydown.escape.window="menuAbierto = false">
    <a href="#contenido" class="skip-link">Ir al contenido</a>
    <div x-show="menuAbierto" x-cloak @click="menuAbierto = false" class="fixed inset-0 bg-black/60 z-30 sm:hidden"></div>
    <aside id="navegacion" class="app-sidebar fixed inset-y-0 left-0 z-40 w-64 flex flex-col transition-transform duration-200 sm:!translate-x-0" :class="menuAbierto ? 'translate-x-0' : '-translate-x-full'">
        <a href="{{ url('/home') }}" class="brand"><img src="{{ asset('sello-moriah-fortaleza.svg') }}" class="brand-logo" width="56" height="56" alt="Moriah Fortaleza"><span>FORTALEZA<small>GESTIÓN FINANCIERA</small></span></a>
        <button @click="menuAbierto = false" class="sm:hidden mx-5 mb-2 text-sm text-slate-300 text-left" aria-label="Cerrar menú">Cerrar menú ×</button>
        <nav class="flex-1 overflow-y-auto px-4 pb-5" aria-label="Navegación principal">@include('layouts.partials.nav-links')</nav>
        <div class="sidebar-account">
            <span class="avatar">{{ mb_substr(auth()->user()?->ct_nombres ?? 'U', 0, 1) }}</span>
            <div class="min-w-0 flex-1"><p class="truncate text-sm font-medium">{{ auth()->user()?->ct_nombres }}</p><p class="text-xs text-slate-400 mt-1">{{ auth()->user()?->perfil?->ct_perfil ?? 'Usuario' }}</p></div>
            <form method="POST" action="{{ url('/logout') }}">@csrf<button type="submit" class="logout-button" aria-label="Salir" title="Salir">↪</button></form>
        </div>
    </aside>
    <div class="sm:ml-64">
        <header class="app-topbar">
            <div class="flex items-center gap-3"><button @click="menuAbierto = !menuAbierto" :aria-expanded="menuAbierto" aria-controls="navegacion" class="sm:hidden text-xl" aria-label="Abrir menú">☰</button><span class="text-slate-400">FORTALEZA <span class="mx-2 text-slate-600">/</span> <span class="text-slate-200">Panel de gestión</span></span></div>
        </header>
        <main id="contenido" class="app-content" tabindex="-1">{{ $slot }}</main>
        <footer class="app-footer">FORTALEZA <div class="footer-actions"><span>Administración y finanzas</span>@include('layouts.partials.theme-toggle')</div></footer>
    </div>
    @livewireScripts
</body>
</html>
