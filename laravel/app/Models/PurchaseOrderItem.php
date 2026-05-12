<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'qty' => 'float',
        'price' => 'float',
        'subtotal' => 'float',
    ];

    public function productions()
    {
        return $this->hasMany(Production::class);
    }

    public function packings()
    {
        return $this->hasManyThrough(
            Packing::class,
            Production::class,
            'purchase_order_item_id',
            'production_id',
            'id',
            'id'
        );
    }

    public function pengirimans()
    {
        return $this->hasMany(Pengiriman::class);
    }

    public function getTotalPackedAttribute()
    {
        return $this->packings()->where('packings.status', 'complete')->sum('total_pcs');
    }

    public function getTotalShippedAttribute()
    {
        return $this->pengirimans()->sum('qty_kirim');
    }

    public function getQtyReadyToShipAttribute()
    {
        return max(0, $this->total_packed - $this->total_shipped);
    }

    public function refreshStage(): void
    {
        $stage = 'draft';

        if ($this->qty_ready_to_ship <= 0 && $this->total_shipped > 0) {
            $stage = 'done';
        } elseif ($this->pengirimans()->exists()) {
            $stage = 'pengiriman';
        } elseif ($this->packings()->where('packings.status', 'complete')->exists()) {
            $stage = 'packing';
        } elseif ($this->productions()->exists()) {
            $stage = 'produksi';
        } elseif ($this->purchaseOrder?->barangMasuks()->exists()) {
            $hasQcResult = BarangMasukItem::query()
                ->whereHas('barangMasuk', fn ($q) => $q->where('purchase_order_id', $this->purchase_order_id))
                ->whereIn('qc_status', ['lolos', 'reject'])
                ->exists();
            $stage = $hasQcResult ? 'qc' : 'barang_datang';
        }

        if (($this->stage ?? null) !== $stage) {
            $this->forceFill(['stage' => $stage])->saveQuietly();
        }
    }

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $qty = max(0, (float) ($model->qty ?? 0));
            $price = max(0, (float) ($model->price ?? 0));
            $model->qty = $qty;
            $model->price = $price;
            $model->subtotal = $qty * $price;
        });

        static::saved(function (self $model) {
            $model->purchaseOrder?->recalcTotals();
            $model->refreshStage();
            $model->purchaseOrder?->refreshStage();
        });

        static::deleted(function (self $model) {
            $model->purchaseOrder?->recalcTotals();
            $model->refreshStage();
            $model->purchaseOrder?->refreshStage();
        });
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
