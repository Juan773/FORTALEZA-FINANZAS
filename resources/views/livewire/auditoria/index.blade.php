<div class="max-w-4xl">
    <h1 class="text-lg font-semibold mb-4">Auditoría</h1>

    <div class="mb-4">
        <label class="block text-xs text-slate-400 mb-1">Tipo de acción</label>
        <select wire:model.live="accion" class="rounded bg-slate-800 border-slate-600 text-sm">
            <option value="">Todas</option>
            @foreach ($acciones as $codigo => $nombre)
                <option value="{{ $codigo }}">{{ $nombre }}</option>
            @endforeach
        </select>
    </div>

    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-slate-400 border-b border-slate-700">
                <th class="py-2">Fecha</th>
                <th>Usuario</th>
                <th>Acción</th>
                <th>Descripción</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($registros as $registro)
                <tr class="border-b border-slate-800">
                    <td class="py-2">{{ $registro->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $registro->cc_user }}</td>
                    <td>{{ $acciones[$registro->accion] ?? $registro->accion }}</td>
                    <td>{{ $registro->descripcion }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">{{ $registros->links() }}</div>
</div>
