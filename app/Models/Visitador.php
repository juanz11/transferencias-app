<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitador extends Model
{
    protected $table = 'visitadores';
    
    protected $fillable = [
        'nombre',
        'email'
    ];

    // Obtener las transferencias de este visitador
    public function transferencias()
    {
        return $this->hasMany(Transferencia::class, 'visitador_id');
    }
}
