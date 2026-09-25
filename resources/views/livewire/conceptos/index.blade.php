<div class="max-w-3xl">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-lg font-semibold">Conceptos</h1>
        <button wire:click="nuevo" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded">
            + Nuevo concepto
        </button>
    </div>

    @if (session('status'))
        <div class="bg-emerald-900/50 border border-emerald-700 text-emerald-200 text-sm rounded p-3 mb-4">
            {{ session('status') }}
        </div>
    @endif

    @if ($mostrandoFormulario)
        <div class="bg-slate-800 rounded-lg p-6 mb-6">
            <h2 class="font-medium mb-4">{{ $cc_concepto ? 'Editar concepto' : 'Nuevo concepto' }}</h2>
            <form wire:submit="guardar" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Tipo</label>
                    <select wire:model="ct_tipo" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                        <option value="I">Ingreso</option>
                        <option value="E">Egreso</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs text-slate-400 mb-1">Nombre</label>
                    <input type="text" wire:model="ct_nombre" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                    @error('ct_nombre') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @if ($cc_concepto)
                    <div class="flex items-end gap-2">
                        <input type="checkbox" wire:model="ct_vigencia" id="ct_vigencia">
                        <label for="ct_vigencia" class="text-sm text-slate-300">Vigente</label>
                    </div>
                @endif
                <div class="md:col-span-4 flex gap-2">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded">Guardar</button>
                    <button type="button" wire:click="cancelar" class="bg-slate-700 hover:bg-slate-600 text-sm px-4 py-2 rounded">Cancelar</button>
                </div>
            </form>
        </div>
    @endif

    <div class="space-y-2">
        @foreach ($conceptos as $concepto)
            <div class="bg-slate-800 rounded-lg">
                <div class="flex items-center justify-between px-4 py-3">
                    <div class="flex items-center gap-3">
                        <span class="text-xs px-2 py-0.5 rounded {{ $concepto->ct_tipo === 'I' ? 'bg-emerald-900 text-emerald-300' : 'bg-red-900 text-red-300' }}">
                            {{ $concepto->ct_tipo === 'I' ? 'Ingreso' : 'Egreso' }}
                        </span>
                        <span class="{{ $concepto->ct_vigencia == 1 ? '' : 'text-slate-500 line-through' }}">{{ $concepto->ct_nombre }}</span>
                    </div>
                    <div class="flex gap-3 text-xs">
                        <button wire:click="alternarSubconceptos({{ $concepto->cc_concepto }})" class="text-slate-400 hover:text-white">
                            Subconceptos ({{ $concepto->subconceptos->where('ct_vigencia', '1')->count() }})
                        </button>
                        <button wire:click="editar({{ $concepto->cc_concepto }})" class="text-indigo-400 hover:text-indigo-300">Editar</button>
                    </div>
                </div>

                @if ($conceptoExpandido === $concepto->cc_concepto)
                    <div class="border-t border-slate-700 px-4 py-3">
                        @foreach ($concepto->subconceptos->where('ct_vigencia', '1') as $sub)
                            <div class="flex items-center justify-between text-sm py-1">
                                <span>{{ $sub->ct_nombre }}</span>
                                <button wire:click="quitarSubconcepto({{ $sub->cc_subconcepto }})" class="text-red-400 hover:text-red-300 text-xs">Quitar</button>
                            </div>
                        @endforeach
                        <form wire:submit="agregarSubconcepto({{ $concepto->cc_concepto }})" class="flex gap-2 mt-2">
                            <input type="text" wire:model="nuevoSubconceptoNombre" placeholder="Nuevo subconcepto"
                                   class="flex-1 rounded bg-slate-700 border-slate-600 text-sm">
                            <button type="submit" class="bg-slate-700 hover:bg-slate-600 text-xs px-3 rounded">Agregar</button>
                        </form>
                        @error('nuevoSubconceptoNombre') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
