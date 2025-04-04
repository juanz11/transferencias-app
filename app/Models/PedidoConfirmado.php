<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoConfirmado extends Model
{
    protected $table = 'pedidos_confirmados';
    
    protected $fillable = [
        'transferencia_confirmada_id',
        'producto_id',
        'cantidad',
        'descuento'
    ];

    public function transferenciaConfirmada()
    {
        return $this->belongsTo(TransferenciaConfirmada::class, 'transferencia_confirmada_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
