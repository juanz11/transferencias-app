<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';
    
    protected $fillable = [
        'nombre',
        'descripcion',
        'precio'
    ];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function pedidosConfirmados()
    {
        return $this->hasMany(PedidoConfirmado::class);
    }
}
