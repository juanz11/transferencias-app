<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';
    
    protected $fillable = [
        'nombre',
        'drogueria'
    ];

    public function drogueria()
    {
        return $this->belongsTo(Drogeria::class, 'drogueria');
    }

    public function transferencias()
    {
        return $this->hasMany(Transferencia::class, 'cliente_id');
    }
}
