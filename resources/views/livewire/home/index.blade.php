<div class="home-dashboard">
    <div class="dashboard-heading"><div><p class="eyebrow">TU ESPACIO DE TRABAJO</p><h1>Bienvenido, {{ $usuario->ct_nombres }}</h1><p class="text-slate-400 mt-2">Todo listo para una gestión más ordenada.</p></div><span class="date-pill">{{ now()->format('d / m / Y') }}</span></div>
    <section class="welcome-panel">
        <div class="relative z-10"><span class="section-tag">FORTALEZA · FINANZAS</span><h2>Cada movimiento,<br>en su lugar.</h2><p>Registra los ingresos, organiza los egresos y consulta<br class="hidden lg:block"> la información de tu comunidad desde un solo lugar.</p><a href="{{ url('/caja/nuevo') }}" class="primary-link">+ Crear nuevo recibo <span aria-hidden="true">↗</span></a></div>
        <div class="hero-art" aria-hidden="true"><div class="art-ring"></div><div class="art-ring second"></div><img src="{{ asset('sello-moriah-fortaleza.svg') }}" class="hero-logo" width="210" height="210" alt=""><span class="art-caption">ORDEN QUE CONSTRUYE.</span></div>
    </section>
    <div class="section-heading"><h2>Accesos rápidos</h2><span>Las tareas de cada día</span></div>
    <div class="quick-grid">
        @foreach ([['/caja', '↙', 'Recibos', 'Consulta y administra los ingresos.'], ['/compras', '↗', 'Egresos', 'Organiza compras y salidas.'], ['/estado-cuenta', '▤', 'Estado de cuenta', 'Revisa los movimientos por socio.'], ['/balances', '◷', 'Balances', 'Consulta los resultados financieros.']] as [$ruta, $icono, $nombre, $descripcion])
            <a href="{{ url($ruta) }}" class="quick-card"><span class="quick-icon" aria-hidden="true">{{ $icono }}</span><span class="card-arrow" aria-hidden="true">↗</span><h3>{{ $nombre }}</h3><p>{{ $descripcion }}</p></a>
        @endforeach
    </div>
    <div class="section-heading"><h2>Tu comunidad, organizada</h2><span>Directorio</span></div>
    <div class="directory-grid">
        @foreach ([['/socios', '01', 'Socios', 'Personas que forman parte de FORTALEZA'], ['/proveedores', '02', 'Proveedores', 'Contactos para tus compras y servicios'], ['/conceptos', '03', 'Conceptos', 'Categorías para cada movimiento']] as [$ruta, $numero, $nombre, $descripcion])
            <a href="{{ url($ruta) }}" class="directory-link"><span class="directory-number">{{ $numero }}</span><div><h3>{{ $nombre }}</h3><p>{{ $descripcion }}</p></div><span aria-hidden="true">→</span></a>
        @endforeach
    </div>
</div>
