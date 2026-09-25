<div class="max-w-5xl">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-lg font-semibold">Socios</h1>
        <button wire:click="nuevo" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded">
            + Nuevo socio
        </button>
    </div>

    @if (session('status'))
        <div class="bg-emerald-900/50 border border-emerald-700 text-emerald-200 text-sm rounded p-3 mb-4">
            {{ session('status') }}
        </div>
    @endif

    @if ($mostrandoFormulario)
        <div class="bg-slate-800 rounded-lg p-6 mb-6">
            <h2 class="font-medium mb-4">{{ $cc_persona ? 'Editar socio '.$cc_persona : 'Nuevo socio' }}</h2>
            <form wire:submit="guardar" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs text-slate-400 mb-1">Nombres completos</label>
                    <input type="text" wire:model="ct_nombres" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                    @error('ct_nombres') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Tipo de usuario</label>
                    <select wire:model="ct_tp_user" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                        @foreach ($catalogos['tipoUsuario'] as $codigo => $etiqueta)
                            <option value="{{ $codigo }}">{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-slate-400 mb-1">Tipo doc.</label>
                    <select wire:model="cp_tipo_doc" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                        @foreach ($catalogos['tipoDoc'] as $codigo => $etiqueta)
                            <option value="{{ $codigo }}">{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">N° documento</label>
                    <input type="text" wire:model="ct_nro_doc" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                    @error('ct_nro_doc') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Sexo</label>
                    <select wire:model="cp_sexo" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                        @foreach ($catalogos['sexo'] as $codigo => $etiqueta)
                            <option value="{{ $codigo }}">{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-slate-400 mb-1">Fecha de nacimiento</label>
                    <input type="date" wire:model="ct_fech_nac" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Estado civil</label>
                    <select wire:model="ct_est_civil" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                        <option value="">—</option>
                        @foreach ($catalogos['estadoCivil'] as $codigo => $etiqueta)
                            <option value="{{ $codigo }}">{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Cónyuge</label>
                    <input type="text" wire:model="ct_conyugue" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                </div>

                <div>
                    <label class="block text-xs text-slate-400 mb-1">Religión</label>
                    <select wire:model="ct_religion" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                        <option value="">—</option>
                        @foreach ($catalogos['religion'] as $codigo => $etiqueta)
                            <option value="{{ $codigo }}">{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Zona</label>
                    <select wire:model="ct_zona" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                        <option value="">—</option>
                        @foreach ($catalogos['zona'] as $codigo => $etiqueta)
                            <option value="{{ $codigo }}">{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">N° de hijos</label>
                    <input type="text" wire:model="ct_hijos" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                </div>

                <div>
                    <label class="block text-xs text-slate-400 mb-1">Profesión</label>
                    <input type="text" wire:model="cc_profesion" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Manzana / lote</label>
                    <input type="text" wire:model="ct_manzana" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Forma de pago habitual</label>
                    <input type="text" wire:model="ct_pago" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                </div>

                <div>
                    <label class="block text-xs text-slate-400 mb-1">Email</label>
                    <input type="email" wire:model="ct_email" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Celular</label>
                    <input type="text" wire:model="ct_celular" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Dirección</label>
                    <input type="text" wire:model="ct_direccion" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                </div>

                <div class="md:col-span-3">
                    <label class="block text-xs text-slate-400 mb-1">Observaciones</label>
                    <textarea wire:model="ct_obs" rows="2" class="w-full rounded bg-slate-700 border-slate-600 text-sm"></textarea>
                </div>

                @if ($cc_persona)
                    <div class="md:col-span-3 flex items-center gap-2">
                        <input type="checkbox" wire:model="cfl_vigencia" id="cfl_vigencia">
                        <label for="cfl_vigencia" class="text-sm text-slate-300">Socio vigente</label>
                    </div>
                @endif

                <div class="md:col-span-3 flex gap-2">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded">Guardar</button>
                    <button type="button" wire:click="cancelar" class="bg-slate-700 hover:bg-slate-600 text-sm px-4 py-2 rounded">Cancelar</button>
                </div>
            </form>
        </div>
    @endif

    <div class="mb-4 flex flex-wrap gap-3 items-end">
        <div>
            <input type="text" wire:model.live.debounce.400ms="buscar" placeholder="Buscar por nombre..."
                   class="w-64 rounded bg-slate-800 border-slate-600 text-sm">
        </div>
        <div>
            <select wire:model.live="filtroTipoUsuario" class="rounded bg-slate-800 border-slate-600 text-sm">
                <option value="">Tipo: todos</option>
                @foreach ($catalogos['tipoUsuario'] as $codigo => $etiqueta)
                    <option value="{{ $codigo }}">{{ $etiqueta }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select wire:model.live="filtroZona" class="rounded bg-slate-800 border-slate-600 text-sm">
                <option value="">Zona: todas</option>
                @foreach ($catalogos['zona'] as $codigo => $etiqueta)
                    <option value="{{ $codigo }}">{{ $etiqueta }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select wire:model.live="filtroVigencia" class="rounded bg-slate-800 border-slate-600 text-sm">
                <option value="">Estado: todos</option>
                <option value="1">Vigente</option>
                <option value="0">Inactivo</option>
            </select>
        </div>
        <button wire:click="exportarExcel" class="bg-slate-700 hover:bg-slate-600 text-xs px-3 py-2 rounded">
            Exportar a Excel
        </button>
    </div>

    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-slate-400 border-b border-slate-700">
                <th class="py-2">Código</th>
                <th>Nombres</th>
                <th>Documento</th>
                <th>Celular</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($socios as $socio)
                <tr class="border-b border-slate-800">
                    <td class="py-2">{{ $socio->codigoFormateado() }}</td>
                    <td>{{ $socio->ct_nombres }}</td>
                    <td>{{ $socio->ct_nro_doc }}</td>
                    <td>{{ $socio->ct_celular }}</td>
                    <td>
                        <span class="{{ $socio->cfl_vigencia == 1 ? 'text-emerald-400' : 'text-slate-500' }}">
                            {{ $socio->cfl_vigencia == 1 ? 'Vigente' : 'Inactivo' }}
                        </span>
                    </td>
                    <td class="text-right">
                        <button wire:click="editar('{{ $socio->cc_persona }}')" class="text-indigo-400 hover:text-indigo-300 text-xs">Editar</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $socios->links() }}
    </div>
</div>
