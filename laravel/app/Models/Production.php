<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
        'qty_produced' => 'float',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function barangMasuk()
    {
        return $this->belongsTo(BarangMasuk::class);
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class); // Finished Product
    }

    public function items()
    {
        return $this->hasMany(ProductionItem::class);
    }

    public function packings()
    {
        return $this->hasMany(Packing::class);
    }

    public function materials()
    {
        return $this->hasMany(ProductionMaterial::class);
    }

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $model->qty_produced = max(0, (float) ($model->qty_produced ?? 0));
        });

        static::saved(function (self $model) {
            if ($model->status === 'completed' && ! $model->packings()->exists()) {
                Packing::create([
                    'production_id' => $model->id,
                    'tanggal_packing' => now(),
                    'total_pcs' => (float) ($model->qty_produced ?? 0),
                    'isi_per_pack' => 0,
                    'total_pack' => 0,
                    'sisa_pcs' => (float) ($model->qty_produced ?? 0),
                    'status' => 'belum_mulai',
                ]);
            }
        });
    }
}
