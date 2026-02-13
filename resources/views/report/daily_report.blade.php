<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: #f2f2f2;
            padding: 8px 6px;
            text-transform: uppercase;
        }
        td {
            padding: 8px 6px;
        }
        h2 {
            text-align: center;
            font-size: 22px;
            width: 100%;
        }
    </style>
</head>
<body>
    <table style="width: 100%; margin-bottom: 0px;">
        <tr>
            <td>
                <img src="{{ public_path('images/logo.png') }}" alt="Logo" width="200">
            </td>
            <td style="text-align: right; color: #00724e; font-size: 20px; font-weight: bold;">
                Nº {{ $report->id }}
            </td>
        </tr>
    </table>
    <h2>REPORTE DIARIO DE MAQUINARIA</h2>

    <table border="1" style="width: 100%; border-collapse: collapse;margin-bottom: 10px;">
        <tr>
            <th style="font-weight: bold;">Fecha:</th>
            <td>{{ $report->date }}</td>
            <th style="font-weight: bold;">Obra:</th>
            <td>{{ $report->project->name }}</td>
        </tr>
        <tr>
            <th style="font-weight: bold;">Nº Interno:</th>
            <td>{{ $report->machine->internal_id }}</td>
            <th style="font-weight: bold;">Maquina:</th>
            <td>{{ $report->machine->plate }}</td>
        </tr>
    </table>
    <table border="1" style="width: 100%; border-collapse: collapse;margin-bottom: 10px;">
        <tr>
            <th colspan="3">INDICAR KILOMETRAJE</th>
            <th colspan="3">INDICAR HOROMETRO</th>
        </tr>
        <tr>
            <td>{{$report->initial_km}}</td>
            <td>{{$report->final_km}}</td>
            <td>{{$report->final_km - $report->initial_km}}</td>
            <td>{{$report->initial_hm}}</td>
            <td>{{$report->final_hm}}</td>
            <td>{{$report->final_hm - $report->initial_hm}}</td>
        </tr>
    </table>
    <table border="1" style="width: 100%; border-collapse: collapse;margin-bottom: 10px;"> 
        <tr>
            <th style="font-weight: bold;">DESCRIPCIÓN DE LOS TRABAJOS REALIZADOS</th>
        </tr>
        <tr>
            <td>{{ $report->work_description }}</td>
        </tr>
    </table>


    <table style="width: 100%">
        <tr>
            <td>
                <div style="font-weight: bold;">{{ $report->user->name }}</div>
                <div style="font-weight: thin;color:gray">{{ $report->user->rut }}</div>
                <div>Operador</div>
                <div>{{ $report->created_at }}</div>
            </td>
        </tr>
    </table>
</body>
</html>