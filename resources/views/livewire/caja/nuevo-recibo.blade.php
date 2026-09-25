<div class="max-w-4xl">
    <h1 class="text-lg font-semibold mb-4">Nuevo recibo</h1>

    @if (session('status'))
        <div class="bg-emerald-900/50 border border-emerald-700 text-emerald-200 text-sm rounded p-3 mb-4">
            {{ session('status') }}
        </div>
    @endif

    <div class="bg-slate-800 rounded-lg p-6 mb-6 space-y-4">
        <div>
            <label class="block text-xs text-slate-400 mb-1">Socio</label>
            @if ($nombreSocio)
                <div class="flex items-center justify-between bg-slate-700 rounded px-3 py-2 text-sm">
                    <span>{{ $nombreSocio }}</span>
                    <button wire:click="$set('cc_persona', null)" class="text-red-400 text-xs">Cambiar</button>
                </div>
            @else
                <input type="text" wire:model.live.debounce.300ms="buscarSocio" placeholder="Buscar por nombre o documento..."
                       class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                @if ($resultados->isNotEmpty())
                    <div class="bg-slate-700 rounded mt-1 divide-y divide-slate-600">
                        @foreach ($resultados as $socio)
                            <button type="button" wire:click="elegirSocio('{{ $socio->cc_persona }}', '{{ addslashes($socio->ct_nombres) }}')"
                                    class="w-full text-left px-3 py-2 text-sm hover:bg-slate-600">
                                {{ $socio->ct_nombres }} — {{ $socio->ct_nro_doc }}
                            </button>
                        @endforeach
                    </div>
                @endif
            @endif
            @error('cc_persona') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs text-slate-400 mb-1">Comprobante</label>
                <select wire:model="ct_serie" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                    <option value="">—</option>
                    @foreach ($comprobantes as $c)
                        <option value="{{ $c->ct_serie }}">{{ $c->ct_documento }} (serie {{ $c->ct_serie }})</option>
                    @endforeach
                </select>
                @error('ct_serie') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Forma de pago</label>
                <select wire:model.live="pag_tipo" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                    <option value="">—</option>
                    <option value="E">Efectivo</option>
                    <option value="V">Voucher</option>
                </select>
                @error('pag_tipo') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        @if ($pag_tipo === 'V')
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Banco</label>
                    <select wire:model.live="cc_banco" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                        <option value="">—</option>
                        @foreach ($bancos as $banco)
                            <option value="{{ $banco->cc_banco }}">{{ $banco->ct_nombre }}</option>
                        @endforeach
                    </select>
                    @error('cc_banco') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Cuenta</label>
                    <select wire:model="cc_cuenta" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                        <option value="">—</option>
                        @foreach ($cuentas as $cuenta)
                            <option value="{{ $cuenta->cc_cuenta }}">{{ $cuenta->ct_numero }}</option>
                        @endforeach
                    </select>
                    @error('cc_cuenta') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">N° operación</label>
                    <input type="text" wire:model="pag_nop" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Fecha operación</label>
                    <input type="date" wire:model="pag_fecha" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                </div>
            </div>
        @endif

        <div>
            <label class="block text-xs text-slate-400 mb-1">Observación</label>
            <input type="text" wire:model="caj_obs" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
        </div>
    </div>

    <div class="bg-slate-800 rounded-lg p-6 mb-6">
        <h2 class="font-medium mb-4">Detalle</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div class="md:col-span-2">
                <label class="block text-xs text-slate-400 mb-1">Concepto</label>
                <select wire:model="nuevoConcepto" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
                    <option value="">—</option>
                    @foreach ($conceptos as $concepto)
                        <option value="{{ $concepto->cc_concepto }}">{{ $concepto->ct_nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Cantidad</label>
                <input type="text" wire:model="nuevaCantidad" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Importe</label>
                <input type="text" wire:model="nuevoImporte" class="w-full rounded bg-slate-700 border-slate-600 text-sm">
            </div>
        </div>
        @error('nuevoConcepto') <p class="text-red-400 text-xs mb-2">{{ $message }}</p> @enderror
        @error('nuevaCantidad') <p class="text-red-400 text-xs mb-2">{{ $message }}</p> @enderror
        @error('nuevoImporte') <p class="text-red-400 text-xs mb-2">{{ $message }}</p> @enderror
        <button wire:click="agregarLinea" class="bg-slate-700 hover:bg-slate-600 text-sm px-4 py-2 rounded mb-4">+ Agregar línea</button>

        @error('carrito') <p class="text-red-400 text-xs mb-2">{{ $message }}</p> @enderror

        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-slate-400 border-b border-slate-700">
                    <th class="py-2">Concepto</th>
                    <th class="text-right">Cantidad</th>
                    <th class="text-right">Importe</th>
                    <th class="text-right">Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($carrito as $i => $linea)
                    <tr class="border-b border-slate-800">
                        <td class="py-2">{{ $linea['nombre'] }}</td>
                        <td class="text-right">{{ $linea['ct_cantidad'] }}</td>
                        <td class="text-right">{{ number_format($linea['ct_importe'], 2) }}</td>
                        <td class="text-right">{{ number_format($linea['ct_total'], 2) }}</td>
                        <td class="text-right">
                            <button wire:click="quitarLinea({{ $i }})" class="text-red-400 text-xs">Quitar</button>
                        </td>
                    </tr>
                @endforeach
                @if (count($carrito))
                    <tr class="font-semibold">
                        <td colspan="3" class="text-right py-2">Total del recibo:</td>
                        <td class="text-right">{{ number_format(collect($carrito)->sum('ct_total'), 2) }}</td>
                        <td></td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <button wire:click="guardar" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-6 py-2 rounded">
        Emitir recibo
    </button>
</div>
