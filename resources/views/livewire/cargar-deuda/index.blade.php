<div class="max-w-2xl">
    <h1 class="text-lg font-semibold mb-4">Cargar deuda</h1>

    @if (session('status'))
        <div class="bg-emerald-900/50 border border-emerald-700 text-emerald-200 text-sm rounded p-3 mb-4">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="guardar" class="bg-slate-800 rounded-lg p-6 space-y-4">
        <div>
            <label class="block text-xs text-slate-400 mb-1">Fecha</label>
            <input type="date" wire:model="ct_fecha" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" wire:model.live="todos" id="todos">
            <label for="todos" class="text-sm text-slate-300">Aplicar a todos los socios activos</label>
        </div>

        @unless ($todos)
            <div>
                <label class="block text-xs text-slate-400 mb-1">Socio</label>
                <select wire:model="cc_persona" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                    <option value="">—</option>
                    @foreach ($socios as $socio)
                        <option value="{{ $socio->cc_persona }}">{{ $socio->ct_nombres }}</option>
                    @endforeach
                </select>
                @error('cc_persona') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        @endunless

        <div>
            <label class="block text-xs text-slate-400 mb-1">Concepto</label>
            <select wire:model="cc_concepto" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                <option value="">—</option>
                @foreach ($conceptos as $concepto)
                    <option value="{{ $concepto->cc_concepto }}">{{ $concepto->ct_nombre }}</option>
                @endforeach
            </select>
            @error('cc_concepto') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs text-slate-400 mb-1">Descripción</label>
            <input type="text" wire:model="cc_descripcion" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
            @error('cc_descripcion') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs text-slate-400 mb-1">Monto</label>
            <input type="text" wire:model="ct_monto" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
            @error('ct_monto') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded">Guardar</button>
    </form>
</div>
