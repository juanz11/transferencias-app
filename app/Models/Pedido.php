<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
