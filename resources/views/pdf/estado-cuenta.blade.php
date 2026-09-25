<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; }
        .detalle th, .detalle td { border: 1px solid #999; padding: 4px; font-size: 11px; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h2>ESTADO DE CUENTA</h2>
    <table>
        <tr>
            <td>Año: {{ $anho }}</td>
            <td class="right">{{ now()->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td colspan="2">DNI: {{ $socio['ct_nro_doc'] }} — {{ $socio['ct_nombres'] }}</td>
        </tr>
    </table>

    <table class="detalle" style="margin-top: 10px;">
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Voucher</th>
                <th>Documento</th>
                <th>Glosa</th>
                <th>Débito</th>
                <th>Crédito</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($movimientos as $i => $mov)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($mov['ct_fecha'])->format('d/m/Y') }}</td>
                    <td>{{ $mov['voucher'] }}</td>
                    <td>{{ $mov['tipo'] }}=&gt;{{ $mov['concepto'] }}</td>
                    <td>{{ $mov['descripcion'] }}</td>
                    <td class="right">{{ number_format($mov['ingreso'], 2) }}</td>
                    <td class="right">{{ number_format($mov['egreso'], 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="5" class="right">Sumas:</td>
                <td class="right"><strong>{{ number_format(abs($totalDebito), 2) }}</strong></td>
                <td class="right"><strong>{{ number_format($totalCredito, 2) }}</strong></td>
            </tr>
            <tr>
                <td colspan="5" class="right">Saldo anterior:</td>
                <td colspan="2" class="right">{{ number_format($saldoAnterior, 2) }}</td>
            </tr>
            <tr>
                <td colspan="5" class="right"><strong>Saldo final:</strong></td>
                <td colspan="2" class="right"><strong>{{ number_format($saldoFinal, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <p style="text-align: center; margin-top: 15px;">
        Usted tiene una deuda de {{ number_format(max($saldoFinal, 0), 2) }}
        ({{ (float) $saldoFinal > 0 ? \App\Support\NumeroALetras::convertir($saldoFinal).' soles' : '0.00 soles' }})
    </p>
</body>
</html>
