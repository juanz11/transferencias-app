<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Visitador;
use App\Models\Cliente;

class Transferencia extends Model
{
    protected $table = 'transferencias';
    
    protected $fillable = [
        'user_id',
        'visitador_id',
        'cliente_id',
        'fecha_correo',
        'fecha_transferencia',
        'transferencia_numero',
        'confirmada'
    ];

    protected $casts = [
        'fecha_correo' => 'date',
        'fecha_transferencia' => 'date',
        'confirmada' => 'boolean'
    ];

    public function visitador()
    {
        return $this->belongsTo(Visitador::class, 'visitador_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function confirmacion()
    {
        return $this->hasOne(TransferenciaConfirmada::class, 'transferencia_id');
    }
}
