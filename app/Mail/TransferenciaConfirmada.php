<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TransferenciaConfirmada extends Mailable
{
    use Queueable, SerializesModels;

    public $transferencia;
    public $calculos;
    public $drogueria;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($transferencia, $calculos, $drogueria)
    {
        $this->transferencia = $transferencia;
        $this->calculos = $calculos;
        $this->drogueria = $drogueria;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.transferencia-confirmada')
                    ->subject('Confirmación de Transferencia #' . $this->transferencia->transferencia->transferencia_numero);
    }
}
