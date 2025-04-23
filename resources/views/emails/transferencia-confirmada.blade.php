@component('mail::message')

<h1>CONFIRMACIÓN TRANSFERENCIA Nº <span style="color: red">{{$transferencia->transferencia->transferencia_numero}}</span></h1>

<p style="text-align: center">Saludos {{$transferencia->transferencia->visitador->nombre}}, su transferencia ha sido confirmada.
<br>
<br>
<hr>
<h1>Datos de la transferencia</h1>
@component('mail::table')
| <span style="color: black; font-weight: bold;">Fecha de notificación:</span> | <span style="color: black; font-weight: bold;">Fecha de Confirmación:</span>              |
| -------------          |:-------------:      |
| {{ date('d-m-Y', strtotime($transferencia->transferencia->fecha_transferencia)) }} | {{ date('d-m-Y', strtotime($transferencia->created_at)) }} |
@endcomponent
<br>
<hr>
<h1>Datos del Cliente</h1>
@component('mail::table')
| <span style="color: black; font-weight: bold;">Cliente</span>                | <span style="color: black; font-weight: bold;">Código</span>              | <span style="color: black; font-weight: bold;">Droguería</span>         |
| -------------          |:-------------:      |:-------------:    |
| {{$transferencia->transferencia->cliente->nombre_cliente}} | {{$transferencia->transferencia->cliente->codigo_cliente}} | {{$drogueria->nombre}} |
@endcomponent
<br>
<hr>
<h1>Productos</h1>
@component('mail::table')
| <span style="color: black; font-weight: bold;">Producto</span>                | <span style="color: black; font-weight: bold;">Cantidad</span>               | <span style="color: black; font-weight: bold;">Comisión</span>               | <span style="color: black; font-weight: bold;">Total</span>                |
| -------------           |:-------------:         |:-------------:         | -------------------: |
@foreach ($calculos as $calculo)
|{{$calculo->productos->nombre}} | {{$calculo->cantidad}} | ${{$calculo->comision}} | ${{round($calculo->total, 2)}} |
@endforeach

@endcomponent
<br><br>
Recuerde que esta es información de su ganancia real por la transferencia procesada, <b>NO OBSTANTE, TODOS LOS DATOS AQUÍ SUMINISTRADOS ESTARÁN SUJETOS A REVISIÓN.</b><br><br>
Atentamente,<br>
{{ str_replace('_', ' ', config('app.name')) }}
@endcomponent
