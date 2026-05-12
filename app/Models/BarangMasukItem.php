<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangMasukItem extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'qty' => 'float',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            if (blank($model->unit) && $model->product_id) {
                $model->unit = Product::query()->whereKey($model->product_id)->value('unit');
            }
            $model->qty = max(0, (float) ($model->qty ?? 0));
        });

        $refresh = function (self $model): void {
            $po = $model->barangMasuk?->purchaseOrder;
            if (! $po) {
                return;
            }

            $po->items()->get()->each->refreshStage();
            $po->refreshStage();
        };

        static::saved($refresh);
        static::deleted($refresh);
    }

    public function barangMasuk()
    {
        return $this->belongsTo(BarangMasuk::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productionMaterials()
    {
        return $this->hasMany(\App\Models\ProductionMaterial::class);
    }
}
