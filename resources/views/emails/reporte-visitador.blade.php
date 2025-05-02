@component('mail::message')

<h2><b>Total de Unidades Vendidas</b> | <span style="color: red">{{ $visitador }}</span></h2>
<p>Desde: {{ $fechaInicio }} - Hasta: {{ $fechaFin }}</p>

________________________________________

<h3>DETALLES DE PRODUCTOS</h3>
@component('mail::table')
| Producto | Unidades | Comisión | Sub-Total (USD) |
| :--- | :---: | :---: | ---: |
@foreach ($productos as $producto)
| {{$producto['nombre']}} | {{$producto['cantidad']}} | ${{$producto['comision']}} | $ {{number_format($producto['subtotal'], 2)}} |
@endforeach
| | | **Total (USD)** | **$ {{number_format($total, 2)}}** |
@endcomponent

________________________________________

<b>TODOS LOS DATOS AQUÍ SUMINISTRADOS ESTARÁN SUJETOS A REVISIÓN.</b><br><br>
Atentamente,<br>
{{ str_replace('_', ' ', config('app.name')) }}
@endcomponent
