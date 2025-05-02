<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReporteVisitador extends Mailable
{
    use Queueable, SerializesModels;

    public $visitador;
    public $fechaInicio;
    public $fechaFin;
    public $productos;
    public $total;

    public function __construct($visitador, $fechaInicio, $fechaFin, $productos, $total)
    {
        $this->visitador = $visitador;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->productos = $productos;
        $this->total = $total;
    }

    public function build()
    {
        return $this->subject('Reporte de Ventas - ' . $this->visitador)
                    ->markdown('emails.reporte-visitador');
    }
}
