<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';
    
    protected $fillable = [
        'nombre',
        'comision'
    ];

    protected $casts = [
        'comision' => 'float'
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
