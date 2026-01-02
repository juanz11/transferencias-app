<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Producto;
use App\Models\Drogeria;

class DiscountRule extends Model
{
    protected $table = 'discount_rules';

    protected $fillable = [
        'producto_id',
        'drogueria_id',
        'min_qty_low',
        'pct_low',
        'min_qty_mid',
        'pct_mid',
        'min_qty_high',
        'pct_high',
        'is_active',
    ];

    protected $casts = [
        'producto_id' => 'int',
        'drogueria_id' => 'int',
        'min_qty_low' => 'int',
        'pct_low' => 'float',
        'min_qty_mid' => 'int',
        'pct_mid' => 'float',
        'min_qty_high' => 'int',
        'pct_high' => 'float',
        'is_active' => 'bool',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function drogueria()
    {
        return $this->belongsTo(Drogeria::class, 'drogueria_id');
    }
}
