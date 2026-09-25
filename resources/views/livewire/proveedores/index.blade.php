<div class="max-w-4xl">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-lg font-semibold">Proveedores</h1>
        <button wire:click="nuevo" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded">
            + Nuevo proveedor
        </button>
    </div>

    @if (session('status'))
        <div class="bg-emerald-900/50 border border-emerald-700 text-emerald-200 text-sm rounded p-3 mb-4">
            {{ session('status') }}
        </div>
    @endif

    @if ($mostrandoFormulario)
        <div class="bg-slate-800 rounded-lg p-6 mb-6">
            <h2 class="font-medium mb-4">{{ $emp_id ? 'Editar proveedor' : 'Nuevo proveedor' }}</h2>
            <form wire:submit="guardar" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Razón social</label>
                    <input type="text" wire:model="emp_razon_social" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                    @error('emp_razon_social') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Nombre comercial</label>
                    <input type="text" wire:model="emp_nom_comercial" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">RUC (11 dígitos)</label>
                    <input type="text" wire:model="emp_ruc" maxlength="11" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                    @error('emp_ruc') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Dirección</label>
                    <input type="text" wire:model="emp_direccion" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Teléfono</label>
                    <input type="text" wire:model="emp_telefono" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Celular</label>
                    <input type="text" wire:model="emp_celular" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Email</label>
                    <input type="email" wire:model="emp_email" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                    @error('emp_email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Web</label>
                    <input type="text" wire:model="emp_web" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" wire:model="emp_estado" id="emp_estado">
                    <label for="emp_estado" class="text-sm text-slate-300">Activo</label>
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
                <th class="py-2">Razón social</th>
                <th>RUC</th>
                <th>Contacto</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($proveedores as $proveedor)
                <tr class="border-b border-slate-800">
                    <td class="py-2">{{ $proveedor->emp_razon_social }}</td>
                    <td>{{ $proveedor->emp_ruc }}</td>
                    <td>{{ $proveedor->emp_celular ?: $proveedor->emp_telefono }}</td>
                    <td>
                        <span class="{{ $proveedor->emp_estado === '1' ? 'text-emerald-400' : 'text-slate-500' }}">
                            {{ $proveedor->emp_estado === '1' ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td class="text-right">
                        <button wire:click="editar({{ $proveedor->emp_id }})" class="text-indigo-400 hover:text-indigo-300 text-xs">Editar</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
