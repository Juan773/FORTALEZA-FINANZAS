<div class="max-w-5xl">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-lg font-semibold">Recibos emitidos</h1>
        <a href="{{ url('/caja/nuevo') }}" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded">
            + Nuevo recibo
        </a>
    </div>

    <div class="mb-4">
        <label class="block text-xs text-slate-400 mb-1">Año</label>
        <select wire:model.live="anho" class="rounded bg-slate-800 border-slate-600 text-sm">
            @foreach (range(now()->year, now()->year - 12) as $a)
                <option value="{{ $a }}">{{ $a }}</option>
            @endforeach
        </select>
    </div>

    @if (session('status'))
        <div class="bg-emerald-900/50 border border-emerald-700 text-emerald-200 text-sm rounded p-3 mb-4">
            {{ session('status') }}
        </div>
    @endif

    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-slate-400 border-b border-slate-700">
                <th class="py-2">Fecha</th>
                <th>Voucher</th>
                <th>Socio</th>
                <th class="text-right">Monto</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($recibos as $recibo)
                <tr class="border-b border-slate-800">
                    <td class="py-2">{{ \Illuminate\Support\Carbon::parse($recibo->caj_fecha)->format('d/m/Y') }}</td>
                    <td>{{ $recibo->voucher }}</td>
                    <td>{{ $recibo->persona?->ct_nombres }}</td>
                    <td class="text-right">{{ number_format($recibo->detalle->sum('ct_total'), 2) }}</td>
                    <td>
                        <span class="{{ $recibo->ct_vigencia == '1' ? 'text-emerald-400' : 'text-slate-500' }}">
                            {{ $recibo->ct_vigencia == '1' ? 'Vigente' : 'Anulado' }}
                        </span>
                    </td>
                    <td class="text-right space-x-3">
                        <a href="{{ url('/caja/'.$recibo->cc_caja.'/pdf') }}" target="_blank" class="text-indigo-400 hover:text-indigo-300 text-xs">
                            PDF
                        </a>
                        @if ($recibo->ct_vigencia == '1')
                            <button wire:click="anular({{ $recibo->cc_caja }})"
                                    wire:confirm="¿Anular este recibo? Esta acción no se puede deshacer."
                                    class="text-red-400 hover:text-red-300 text-xs">
                                Anular
                            </button>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">{{ $recibos->links() }}</div>
</div>
