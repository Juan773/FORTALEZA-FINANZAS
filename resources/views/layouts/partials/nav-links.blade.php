@php
    $grupos = [
        'GENERAL' => [['/home', 'Inicio', '⌂']],
        'MOVIMIENTOS' => [['/caja', 'Recibos', '↙'], ['/caja/nuevo', 'Nuevo recibo', '+'], ['/cargar-deuda', 'Cargar deuda', '≡'], ['/compras', 'Egresos', '↗']],
        'CONSULTAS' => [['/estado-cuenta', 'Estado de cuenta', '▤'], ['/balances', 'Balances', '◷']],
        'DIRECTORIO' => [['/socios', 'Socios', '♧'], ['/conceptos', 'Conceptos', '▦'], ['/proveedores', 'Proveedores', '◇']],
    ];
    if (auth()->user()?->perfil?->ct_perfil === 'Administrador') {
        $grupos['ADMINISTRACIÓN'] = [['/usuarios', 'Usuarios', '♧'], ['/perfiles', 'Perfiles', '▣'], ['/auditoria', 'Auditoría', '◎']];
    }
@endphp
@foreach ($grupos as $nombre => $enlaces)
    <div class="nav-group">
        <p class="nav-heading">{{ $nombre }}</p>
        @foreach ($enlaces as [$ruta, $etiqueta, $icono])
            @php($activo = request()->is(ltrim($ruta, '/')))
            <a href="{{ url($ruta) }}" class="nav-link {{ $activo ? 'is-active' : '' }}" @if($activo) aria-current="page" @endif>
                <span class="nav-icon" aria-hidden="true">{{ $icono }}</span>{{ $etiqueta }}
                @if($activo)<span class="active-dot" aria-hidden="true"></span>@endif
            </a>
        @endforeach
    </div>
@endforeach
