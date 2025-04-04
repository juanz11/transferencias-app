<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Visitador;

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

    public function confirmacion()
    {
        return $this->hasOne(TransferenciaConfirmada::class, 'transferencia_id');
    }
}
