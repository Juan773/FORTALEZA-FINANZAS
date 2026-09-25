<div class="max-w-2xl">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-lg font-semibold">Perfiles</h1>
        <button wire:click="nuevo" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded">
            + Nuevo perfil
        </button>
    </div>

    @if (session('status'))
        <div class="bg-emerald-900/50 border border-emerald-700 text-emerald-200 text-sm rounded p-3 mb-4">
            {{ session('status') }}
        </div>
    @endif

    @if ($mostrandoFormulario)
        <div class="bg-slate-800 rounded-lg p-6 mb-6">
            <h2 class="font-medium mb-4">{{ $cc_perfil ? 'Editar perfil' : 'Nuevo perfil' }}</h2>
            <form wire:submit="guardar" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Nombre</label>
                    <input type="text" wire:model="ct_perfil" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                    @error('ct_perfil') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @if ($cc_perfil)
                    <div class="flex items-end gap-2">
                        <input type="checkbox" wire:model="cfl_vigencia" id="cfl_vigencia">
                        <label for="cfl_vigencia" class="text-sm text-slate-300">Vigente</label>
                    </div>
                @endif
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
                <th class="py-2">Nombre</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($perfiles as $perfil)
                <tr class="border-b border-slate-800">
                    <td class="py-2">{{ $perfil->ct_perfil }}</td>
                    <td>
                        <span class="{{ $perfil->cfl_vigencia == 1 ? 'text-emerald-400' : 'text-slate-500' }}">
                            {{ $perfil->cfl_vigencia == 1 ? 'Vigente' : 'Inactivo' }}
                        </span>
                    </td>
                    <td class="text-right">
                        <button wire:click="editar({{ $perfil->cc_perfil }})" class="text-indigo-400 hover:text-indigo-300 text-xs">Editar</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
