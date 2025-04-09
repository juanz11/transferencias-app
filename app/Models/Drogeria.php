<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Drogeria extends Model
{
    protected $table = 'droguerias';
    
    protected $fillable = [
        'nombre'
    ];

    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'drogueria');
    }
}
