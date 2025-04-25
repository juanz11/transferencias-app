<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TransferenciaConfirmada;
use App\Models\Producto;

class Pedido extends Model
{
    protected $table = 'pedidos';
    
    protected $fillable = [
        'transferencia_id',
        'producto_id',
        'cantidad',
        'descuento'
    ];

    public function transferencia()
    {
        return $this->belongsTo(Transferencia::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function transferenciaConfirmada()
    {
        return $this->belongsTo(TransferenciaConfirmada::class);
    }
}
