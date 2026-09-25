<div class="max-w-5xl">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-lg font-semibold">Egresos / Compras</h1>
        <div class="flex gap-2">
            <button wire:click="alternarResumen" class="bg-slate-700 hover:bg-slate-600 text-sm px-4 py-2 rounded">
                {{ $mostrandoResumen ? 'Ver listado' : 'Resumen por concepto' }}
            </button>
            <button wire:click="nuevo" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded">
                + Nuevo egreso
            </button>
        </div>
    </div>

    @if ($mostrandoResumen)
        <div class="bg-slate-800 rounded-lg p-6 mb-6">
            <form wire:submit="buscarResumen" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Año</label>
                    <select wire:model="resumenAnho" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                        <option value="">—</option>
                        @foreach (range(now()->year, now()->year - 12) as $a)
                            <option value="{{ $a }}">{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Mes</label>
                    <select wire:model="resumenPeriodo" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                        <option value="">—</option>
                        @foreach (['01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto','09'=>'Setiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre'] as $codigo => $nombre)
                            <option value="{{ $codigo }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded">Buscar</button>
                </div>
            </form>

            @if ($resumenFilas)
                <div class="mb-3">
                    <button wire:click="exportarResumenExcel" class="bg-slate-700 hover:bg-slate-600 text-xs px-3 py-1.5 rounded">
                        Exportar a Excel
                    </button>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-400 border-b border-slate-700">
                            <th class="py-2">Código</th>
                            <th>Concepto</th>
                            <th class="text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($resumenFilas as $fila)
                            <tr class="border-b border-slate-800">
                                <td class="py-2">{{ $fila->cc_concepto }}</td>
                                <td>{{ $fila->ct_nombre }}</td>
                                <td class="text-right">{{ number_format($fila->monto, 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="font-semibold border-t border-slate-700">
                            <td colspan="2" class="text-right py-2">Total:</td>
                            <td class="text-right">{{ number_format(collect($resumenFilas)->sum('monto'), 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            @endif
        </div>
    @endif

    @if (session('status'))
        <div class="bg-emerald-900/50 border border-emerald-700 text-emerald-200 text-sm rounded p-3 mb-4">
            {{ session('status') }}
        </div>
    @endif

    @if ($mostrandoFormulario)
        <div class="bg-slate-800 rounded-lg p-6 mb-6">
            <h2 class="font-medium mb-4">{{ $cc_compras ? 'Editar egreso' : 'Nuevo egreso' }}</h2>
            <form wire:submit="guardar" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">RUC proveedor (11 dígitos)</label>
                    <input type="text" wire:model="emp_ruc" maxlength="11" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                    @error('emp_ruc') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs text-slate-400 mb-1">Razón social</label>
                    <input type="text" wire:model="emp_razon_social" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                    @error('emp_razon_social') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs text-slate-400 mb-1">Comprobante</label>
                    <select wire:model="ct_comprobante" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                        <option value="">—</option>
                        @foreach ($comprobantes as $codigo => $etiqueta)
                            <option value="{{ $codigo }}">{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                    @error('ct_comprobante') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Serie</label>
                    <input type="text" wire:model="ct_serie" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Número</label>
                    <input type="text" wire:model="ct_numero" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                </div>

                <div>
                    <label class="block text-xs text-slate-400 mb-1">Concepto (egreso)</label>
                    <select wire:model.live="cc_concepto" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                        <option value="">—</option>
                        @foreach ($conceptos as $concepto)
                            <option value="{{ $concepto->cc_concepto }}">{{ $concepto->ct_nombre }}</option>
                        @endforeach
                    </select>
                    @error('cc_concepto') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Subconcepto</label>
                    <select wire:model="cc_subconcepto" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                        <option value="">—</option>
                        @foreach ($subconceptos as $sub)
                            <option value="{{ $sub->cc_subconcepto }}">{{ $sub->ct_nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Monto</label>
                    <input type="text" wire:model="ct_monto" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                    @error('ct_monto') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs text-slate-400 mb-1">Fecha del comprobante</label>
                    <input type="date" wire:model="ct_fecha_doc" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs text-slate-400 mb-1">Glosa</label>
                    <input type="text" wire:model="ct_glosa" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                    @error('ct_glosa') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-3">
                    <label class="block text-xs text-slate-400 mb-1">Observación</label>
                    <input type="text" wire:model="ct_observacion" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                </div>

                <div class="md:col-span-3 flex gap-2">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded">Guardar</button>
                    <button type="button" wire:click="cancelar" class="bg-slate-700 hover:bg-slate-600 text-sm px-4 py-2 rounded">Cancelar</button>
                </div>
            </form>
        </div>
    @endif

    @unless ($mostrandoResumen)
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-slate-400 border-b border-slate-700">
                    <th class="py-2">Fecha doc.</th>
                    <th>Proveedor</th>
                    <th>Glosa</th>
                    <th class="text-right">Monto</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($compras as $compra)
                    <tr class="border-b border-slate-800">
                        <td class="py-2">{{ $compra->ct_fecha_doc ? \Illuminate\Support\Carbon::parse($compra->ct_fecha_doc)->format('d/m/Y') : '—' }}</td>
                        <td>{{ $compra->emp_razon_social }}</td>
                        <td>{{ $compra->ct_glosa }}</td>
                        <td class="text-right">{{ number_format($compra->ct_monto, 2) }}</td>
                        <td class="text-right">
                            <button wire:click="editar({{ $compra->cc_compras }})" class="text-indigo-400 hover:text-indigo-300 text-xs">Editar</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">{{ $compras->links() }}</div>
    @endunless
</div>
