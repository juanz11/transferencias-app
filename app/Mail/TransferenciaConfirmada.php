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
        $appName = str_replace('_', ' ', config('app.name'));
        return $this->markdown('emails.transferencia-confirmada')
                    ->subject($appName . ' - Confirmación #' . $this->transferencia->transferencia->transferencia_numero)
                    ->with(['appName' => $appName]);
    }
}
