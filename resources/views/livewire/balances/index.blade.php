<div class="max-w-5xl">
    <h1 class="text-lg font-semibold mb-4">Balances</h1>

    <div class="flex gap-2 mb-6">
        <button wire:click="cambiarPestana('concepto')"
                class="text-sm px-4 py-2 rounded {{ $pestana === 'concepto' ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-300' }}">
            Por concepto
        </button>
        <button wire:click="cambiarPestana('socio')"
                class="text-sm px-4 py-2 rounded {{ $pestana === 'socio' ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-300' }}">
            Por socio (asociados)
        </button>
    </div>

    @if ($pestana === 'concepto')
        <form wire:submit="buscarPorConcepto" class="bg-slate-800 rounded-lg p-6 mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs text-slate-400 mb-1">Año (acumulado hasta)</label>
                <select wire:model="anho" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                    @foreach ($anhos as $a)
                        <option value="{{ $a }}">{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Mes (exacto, opcional)</label>
                <select wire:model="periodo" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                    <option value="">—</option>
                    @foreach ($meses as $codigo => $nombre)
                        <option value="{{ $codigo }}">{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded">Buscar</button>
            </div>
        </form>

        @if ($buscado)
            <div class="mb-3">
                <button wire:click="exportarConceptoExcel" class="bg-slate-700 hover:bg-slate-600 text-xs px-3 py-1.5 rounded">
                    Exportar a Excel
                </button>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-400 border-b border-slate-700">
                        <th class="py-2">#</th>
                        <th>Código</th>
                        <th>Concepto</th>
                        <th class="text-right">Debe</th>
                        <th class="text-right">Haber</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($filas as $i => $fila)
                        <tr class="border-b border-slate-800">
                            <td class="py-2">{{ $i + 1 }}</td>
                            <td>{{ $fila['cc_concepto'] }}</td>
                            <td>{{ $fila['ct_nombre'] }}</td>
                            <td class="text-right">{{ number_format($fila['debe'], 2) }}</td>
                            <td class="text-right">{{ number_format($fila['haber'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="font-semibold border-t border-slate-700">
                        <td colspan="3" class="text-right py-2">Total:</td>
                        <td class="text-right">{{ number_format(collect($filas)->sum('debe'), 2) }}</td>
                        <td class="text-right">{{ number_format(collect($filas)->sum('haber'), 2) }}</td>
                    </tr>
                </tbody>
            </table>
        @endif
    @else
        <form wire:submit="buscarPorSocio" class="bg-slate-800 rounded-lg p-6 mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs text-slate-400 mb-1">Concepto (opcional)</label>
                <select wire:model="cc_concepto" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                    <option value="">—</option>
                    @foreach ($conceptos as $concepto)
                        <option value="{{ $concepto->cc_concepto }}">{{ $concepto->ct_nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Estado del socio</label>
                <select wire:model="cfl_vigencia" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                    <option value="">—</option>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded">Buscar</button>
            </div>
        </form>

        @if ($buscado)
            <div class="mb-3">
                <button wire:click="exportarSocioExcel" class="bg-slate-700 hover:bg-slate-600 text-xs px-3 py-1.5 rounded">
                    Exportar a Excel
                </button>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-400 border-b border-slate-700">
                        <th class="py-2">#</th>
                        <th>Nombre</th>
                        <th>DNI</th>
                        <th class="text-right">Debe</th>
                        <th class="text-right">Haber</th>
                        <th class="text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($filas as $i => $fila)
                        <tr class="border-b border-slate-800">
                            <td class="py-2">{{ $i + 1 }}</td>
                            <td>{{ $fila->ct_nombres }}</td>
                            <td>{{ $fila->ct_nro_doc }}</td>
                            <td class="text-right">{{ number_format($fila->debe, 2) }}</td>
                            <td class="text-right">{{ number_format($fila->haber, 2) }}</td>
                            <td class="text-right">{{ number_format($fila->saldo, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="font-semibold border-t border-slate-700">
                        <td colspan="3" class="text-right py-2">Totales:</td>
                        <td class="text-right">{{ number_format(collect($filas)->sum('debe'), 2) }}</td>
                        <td class="text-right">{{ number_format(collect($filas)->sum('haber'), 2) }}</td>
                        <td class="text-right">{{ number_format(collect($filas)->sum('saldo'), 2) }}</td>
                    </tr>
                </tbody>
            </table>
        @endif
    @endif
</div>
