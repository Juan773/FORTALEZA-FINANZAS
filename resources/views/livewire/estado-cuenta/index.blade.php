<div class="max-w-5xl">
    <h1 class="text-lg font-semibold mb-4">Estado de cuenta del asociado</h1>

    <form wire:submit="buscar" class="bg-slate-800 rounded-lg p-6 mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-xs text-slate-400 mb-1">Año</label>
            <select wire:model="anho" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                @foreach ($anhos as $a)
                    <option value="{{ $a }}">{{ $a }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-slate-400 mb-1">DNI del socio</label>
            <input type="text" wire:model="ct_nro_doc" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
            @error('ct_nro_doc') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div class="md:col-span-2">
            <label class="block text-xs text-slate-400 mb-1">Concepto (opcional)</label>
            <select wire:model="cc_concepto" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                <option value="">—</option>
                @foreach ($conceptos as $concepto)
                    <option value="{{ $concepto->cc_concepto }}">{{ $concepto->ct_nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-4">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded">Buscar</button>
        </div>
    </form>

    @if ($buscado && $socio)
        <div class="bg-slate-800 rounded-lg p-6">
            <div class="flex justify-between mb-4">
                <div>
                    <p class="text-sm text-slate-400">DNI: {{ $socio['ct_nro_doc'] }}</p>
                    <p class="font-medium">{{ $socio['ct_nombres'] }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-slate-400 mb-1">Año {{ $anho }}</p>
                    <a href="{{ url('/estado-cuenta/pdf').'?'.http_build_query(['anho' => $anho, 'ct_nro_doc' => $ct_nro_doc, 'cc_concepto' => $cc_concepto]) }}"
                       target="_blank" class="text-indigo-400 hover:text-indigo-300 text-xs">
                        Descargar PDF
                    </a>
                </div>
            </div>

            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-400 border-b border-slate-700">
                        <th class="py-2">#</th>
                        <th>Fecha</th>
                        <th>Voucher</th>
                        <th>Documento</th>
                        <th>Glosa</th>
                        <th class="text-right">Débito</th>
                        <th class="text-right">Crédito</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($movimientos as $i => $mov)
                        <tr class="border-b border-slate-800">
                            <td class="py-2">{{ $i + 1 }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($mov['ct_fecha'])->format('d/m/Y') }}</td>
                            <td>{{ $mov['voucher'] }}</td>
                            <td>{{ $mov['tipo'] }}=>{{ $mov['concepto'] }}</td>
                            <td>{{ $mov['descripcion'] }}</td>
                            <td class="text-right">{{ number_format($mov['ingreso'], 2) }}</td>
                            <td class="text-right">{{ number_format($mov['egreso'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="border-t border-slate-700 font-medium">
                        <td colspan="5" class="text-right py-2">Sumas:</td>
                        <td class="text-right">{{ number_format(abs($totalDebito), 2) }}</td>
                        <td class="text-right">{{ number_format($totalCredito, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-right py-1">Saldo anterior:</td>
                        <td colspan="2" class="text-right">{{ number_format($saldoAnterior, 2) }}</td>
                    </tr>
                    <tr class="font-semibold">
                        <td colspan="5" class="text-right py-1">Saldo final:</td>
                        <td colspan="2" class="text-right">{{ number_format($saldoFinal, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <p class="text-center text-sm text-slate-300 mt-6">
                Usted tiene una deuda de {{ number_format(max($saldoFinal, 0), 2) }} ({{ $this->deudaEnLetras() }})
            </p>
        </div>
    @elseif ($buscado)
        <p class="text-slate-400 text-sm">No se encontró información para ese documento.</p>
    @endif
</div>
