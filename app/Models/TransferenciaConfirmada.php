<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferenciaConfirmada extends Model
{
    protected $table = 'transferencias_confirmadas';
    
    protected $fillable = [
        'user_id',
        'transferencia_id',
        'factura_path'
    ];

    public function transferencia()
    {
        return $this->belongsTo(Transferencia::class, 'transferencia_id');
    }
}
