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
    <h2>ESTADO DE CUENTA — TODOS LOS SOCIOS</h2>
    <table>
        <tr>
            <td>{{ $anho !== '' ? 'Año: '.$anho : 'Todos los años' }}</td>
            <td class="right">{{ now()->format('d/m/Y H:i') }}</td>
        </tr>
    </table>

    <table class="detalle" style="margin-top: 10px;">
        <thead>
            <tr>
                <th>#</th>
                <th>DNI</th>
                <th>Socio</th>
                <th>Saldo anterior</th>
                <th>Débito</th>
                <th>Crédito</th>
                <th>Saldo final</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($resumenSocios as $i => $fila)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $fila['ct_nro_doc'] }}</td>
                    <td>{{ $fila['ct_nombres'] }}</td>
                    <td class="right">{{ number_format($fila['saldo_anterior'], 2) }}</td>
                    <td class="right">{{ number_format(abs($fila['debito']), 2) }}</td>
                    <td class="right">{{ number_format($fila['credito'], 2) }}</td>
                    <td class="right">{{ number_format($fila['saldo_final'], 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="3" class="right"><strong>Totales:</strong></td>
                <td class="right"><strong>{{ number_format(collect($resumenSocios)->sum('saldo_anterior'), 2) }}</strong></td>
                <td class="right"><strong>{{ number_format(abs(collect($resumenSocios)->sum('debito')), 2) }}</strong></td>
                <td class="right"><strong>{{ number_format(collect($resumenSocios)->sum('credito'), 2) }}</strong></td>
                <td class="right"><strong>{{ number_format(collect($resumenSocios)->sum('saldo_final'), 2) }}</strong></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
