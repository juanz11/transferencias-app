<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Charla extends Model
{
    protected $fillable = [
        'fecha',
        'cliente_id',
        'zona',
        'cantidad_charlas',
        'participantes',
        'visitador_id',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function visitador()
    {
        return $this->belongsTo(Visitador::class, 'visitador_id');
    }
}
