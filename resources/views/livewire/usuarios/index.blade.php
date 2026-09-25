<div class="max-w-3xl">
    <h1 class="text-lg font-semibold mb-4">Usuarios del sistema</h1>

    @if (session('status'))
        <div class="bg-emerald-900/50 border border-emerald-700 text-emerald-200 text-sm rounded p-3 mb-4">
            {{ session('status') }}
        </div>
    @endif

    @if (! $mostrandoFormulario)
        <div class="bg-slate-800 rounded-lg p-6 mb-6">
            <h2 class="font-medium mb-3">Dar acceso a un socio nuevo</h2>
            <input type="text" wire:model.live.debounce.300ms="buscarSocio" placeholder="Buscar socio por nombre..."
                   class="w-full rounded bg-slate-700 border-slate-600 text-sm">
            @if ($resultadosSocios->isNotEmpty())
                <div class="bg-slate-700 rounded mt-2 divide-y divide-slate-600">
                    @foreach ($resultadosSocios as $socio)
                        <button type="button" wire:click="elegirSocioNuevo('{{ $socio->cc_persona }}', '{{ addslashes($socio->ct_nombres) }}')"
                                class="w-full text-left px-3 py-2 text-sm hover:bg-slate-600">
                            {{ $socio->ct_nombres }} — {{ $socio->ct_nro_doc }}
                        </button>
                    @endforeach
                </div>
            @endif
            <p class="text-xs text-slate-500 mt-2">Solo aparecen socios que todavía no tienen usuario.</p>
        </div>
    @endif

    @if ($mostrandoFormulario)
        <div class="bg-slate-800 rounded-lg p-6 mb-6">
            <h2 class="font-medium mb-4">{{ $cc_usuario ? 'Editar usuario' : 'Nuevo usuario' }}</h2>
            <p class="text-sm text-slate-400 mb-4">{{ $nombreSocioNuevo }}</p>
            <form wire:submit="guardar" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Usuario (login)</label>
                    <input type="text" wire:model="cc_user" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                    @error('cc_user') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Perfil</label>
                    <select wire:model="cc_perfil" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                        <option value="">—</option>
                        @foreach ($perfiles as $perfil)
                            <option value="{{ $perfil->cc_perfil }}">{{ $perfil->ct_perfil }}</option>
                        @endforeach
                    </select>
                    @error('cc_perfil') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                @if ($cc_usuario)
                    <div class="md:col-span-2">
                        <label class="block text-xs text-slate-400 mb-1">Nueva clave (dejar en blanco para no cambiarla)</label>
                        <input type="password" wire:model="ct_clave" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                    </div>
                @else
                    <div class="md:col-span-2 bg-slate-900/50 rounded p-3 text-xs text-slate-400">
                        La clave inicial será el número de documento del socio. Se le debe pedir que la cambie
                        apenas ingrese por primera vez.
                    </div>
                @endif

                <div class="flex items-center gap-2">
                    <input type="checkbox" wire:model="cfl_acceso" id="cfl_acceso">
                    <label for="cfl_acceso" class="text-sm text-slate-300">Acceso habilitado</label>
                </div>

                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded">Guardar</button>
                    <button type="button" wire:click="cancelar" class="bg-slate-700 hover:bg-slate-600 text-sm px-4 py-2 rounded">Cancelar</button>
                </div>
            </form>
        </div>
    @endif

    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-slate-400 border-b border-slate-700">
                <th class="py-2">Usuario</th>
                <th>Nombre</th>
                <th>Perfil</th>
                <th>Acceso</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($usuarios as $usuario)
                <tr class="border-b border-slate-800">
                    <td class="py-2">{{ $usuario->cc_user }}</td>
                    <td>{{ $usuario->ct_nombres }}</td>
                    <td>{{ $usuario->perfil?->ct_perfil }}</td>
                    <td>
                        <span class="{{ $usuario->cfl_acceso == 1 ? 'text-emerald-400' : 'text-slate-500' }}">
                            {{ $usuario->cfl_acceso == 1 ? 'Habilitado' : 'Deshabilitado' }}
                        </span>
                    </td>
                    <td class="text-right">
                        <button wire:click="editar('{{ $usuario->cc_usuario }}')" class="text-indigo-400 hover:text-indigo-300 text-xs">Editar</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
