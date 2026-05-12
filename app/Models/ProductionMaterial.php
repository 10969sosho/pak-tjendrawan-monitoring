<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionMaterial extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'qty_used' => 'float',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $model->qty_used = max(0, (float) ($model->qty_used ?? 0));
        });
    }

    public function production()
    {
        return $this->belongsTo(Production::class);
    }

    public function barangMasukItem()
    {
        return $this->belongsTo(BarangMasukItem::class);
    }
}

