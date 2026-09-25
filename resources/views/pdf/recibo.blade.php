<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h2 { text-align: center; margin: 0; }
        .center { text-align: center; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 3px; }
        .detalle th, .detalle td { border: 1px solid #999; padding: 4px; font-size: 11px; }
        .right { text-align: right; }
        hr { border: none; border-top: 1px solid #333; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td width="70%">
                <h2>RECIBO INTERNO</h2>
                <p class="center"><strong>ASOCIACIÓN FORTALEZA</strong></p>
                <p class="center">Alameda de Ñaña 2560 - Chosica</p>
            </td>
            <td width="30%" class="center">
                <p>N° {{ $recibo->voucher }}</p>
                <p>{{ $recibo->cc_usuario }}</p>
            </td>
        </tr>
    </table>

    <table>
        <tr>
            <td>DNI: <strong>{{ $recibo->persona?->ct_nro_doc }}</strong></td>
            <td>Nombre: <strong>{{ $recibo->persona?->ct_nombres }}</strong></td>
            <td>Fecha: {{ \Illuminate\Support\Carbon::parse($recibo->caj_fecha)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td>Tp: {{ $recibo->pag_tipo === 'E' ? 'Efectivo' : 'Voucher' }}</td>
            <td>NOP: {{ $recibo->pag_nop }}</td>
            <td>{{ $recibo->banco?->ct_nombre }} {{ $recibo->cuenta?->ct_numero }}</td>
        </tr>
    </table>

    <hr>

    <table class="detalle">
        <thead>
            <tr>
                <th>Item</th>
                <th>Cant.</th>
                <th>Concepto</th>
                <th>P. Unit.</th>
                <th>Sub-total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($recibo->detalle as $i => $linea)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $linea->ct_cantidad }}</td>
                    <td>{{ $linea->concepto?->ct_nombre }}</td>
                    <td class="right">{{ number_format($linea->ct_importe, 2) }}</td>
                    <td class="right">{{ number_format($linea->ct_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p>Obs: {{ $recibo->caj_obs }}</p>
    <table>
        <tr>
            <td>Son: {{ \App\Support\NumeroALetras::convertir($recibo->detalle->sum('ct_total')) }} soles</td>
            <td class="right"><strong>TOTAL: {{ number_format($recibo->detalle->sum('ct_total'), 2) }}</strong></td>
        </tr>
    </table>
</body>
</html>
