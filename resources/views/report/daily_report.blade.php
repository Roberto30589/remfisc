<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>

    <style>
        body { font-family: Arial, sans-serif; font-size:12px; }

        table { width:100%; border-collapse:collapse; }

        th{
            background:#f2f2f2;
            padding:6px;
            text-transform:uppercase;
            font-size:11px;
        }

        td{ padding:6px; font-size:11px; }

        h2{
            text-align:center;
            font-size:20px;
            margin:5px 0 12px 0;
        }

        .border{ border:1px solid #000; }

        .section{ margin-bottom:10px; }

        .unit{
            font-size:10px;
            color:#666;
        }

    </style>
</head>

<body>


<!-- HEADER -->
<table class="section">
<tr>

<td>
<img src="{{ public_path('images/logo.png') }}" width="170">
</td>

<td style="text-align:right;color:#00724e;font-size:18px;font-weight:bold;">
Nº {{ $report->id }}<br>


</td>

</tr>
</table>


<h2>REPORTE DIARIO DE MAQUINARIA</h2>


<!-- INFO GENERAL -->
<table border="1" class="section">

<tr>
<th>Fecha</th>
<td>{{ optional($report->date)->format('d-m-Y') }}</td>

<th>Obra</th>
<td>{{ $report->project->name ?? '-' }}</td>
</tr>

<tr>
<th>Nº Interno</th>
<td>{{ $report->machine->internal_id ?? '-' }}</td>

<th>Máquina</th>
<td>{{ $report->machine->plate ?? '-' }}</td>
</tr>

</table>



<!-- KM / HM -->
<table border="1" class="section">

<tr>
<th colspan="3">Kilometraje</th>
<th colspan="3">Horómetro</th>
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



<!-- TRABAJO -->
<table border="1" class="section">
<tr>
<th>Descripción de los trabajos realizados</th>
</tr>
<tr>
<td>{{ $report->work_description }}</td>
</tr>
</table>



<!-- MANTENCIONES -->
@if($report->maintenances && $report->maintenances->count())

<table border="1" class="section">

<tr>
<th colspan="3">DETALLE DE MANTENCIONES</th>
</tr>

<tr>
<th>Tipo</th>
<th>Cantidad</th>
<th>Observación</th>
</tr>

@foreach($report->maintenances as $m)

<tr>

<td>

{{ $m->maintenanceType->name ?? '-' }}

@if($m->maintenanceType?->unit)
<span class="unit">
({{ $m->maintenanceType->unit }})
</span>
@endif

</td>

<td>
{{ $m->quantity ?? '-' }}
</td>

<td>
{{ $m->observation ?? '-' }}
</td>

</tr>

@endforeach

</table>

@endif



<!-- FIRMA -->
<table style="margin-top:30px">

<tr>
<td>

<div style="font-weight:bold;">
{{ $report->user->name }}
</div>

<div style="color:gray">
{{ $report->user->rut }}
</div>

<div>Operador</div>

<div style="font-size:10px;">
<span style="font-size:10px;color:#000;">
Creado: {{ optional($report->created_at)->format('d-m-Y H:i') }}
</span><br>

@if($report->finished_at)
<span style="font-size:10px;color:#000;">
Cierre: {{ $report->finished_at->format('d-m-Y H:i') }}
</span>
@endif
</div>

</td>

</tr>

</table>


</body>
</html>