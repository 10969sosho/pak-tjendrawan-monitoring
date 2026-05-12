<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
        'deadline' => 'date',
        'total_amount' => 'float',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            if ($model->customer_id && blank($model->supplier_customer)) {
                $model->supplier_customer = $model->customer?->name
                    ?? Customer::query()->whereKey($model->customer_id)->value('name')
                    ?? '-';
            }
        });

        static::saved(function (self $model) {
            $model->refreshStage();
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function penawaran()
    {
        return $this->belongsTo(Penawaran::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function barangMasuks()
    {
        return $this->hasMany(BarangMasuk::class);
    }

    public function productions()
    {
        return $this->hasMany(Production::class);
    }

    public function packings()
    {
        return $this->hasManyThrough(
            Packing::class,
            Production::class,
            'purchase_order_id',
            'production_id',
            'id',
            'id'
        );
    }

    public function pengirimans()
    {
        return $this->hasMany(Pengiriman::class);
    }

    public function refreshStage(): void
    {
        $stage = 'draft';

        if ($this->items()->exists()) {
            $stages = $this->items()->pluck('stage')->all();
            $unique = array_values(array_unique(array_filter($stages)));

            if ($unique !== [] && count($unique) === 1 && $unique[0] === 'done') {
                $stage = 'done';
            } elseif (in_array('pengiriman', $unique, true)) {
                $stage = 'pengiriman';
            } elseif (in_array('packing', $unique, true)) {
                $stage = 'packing';
            } elseif (in_array('produksi', $unique, true)) {
                $stage = 'produksi';
            } elseif (in_array('qc', $unique, true)) {
                $stage = 'qc';
            } elseif (in_array('barang_datang', $unique, true)) {
                $stage = 'barang_datang';
            }
        }

        if (($this->stage ?? null) !== $stage) {
            $this->forceFill(['stage' => $stage])->saveQuietly();
        }

        $allItemsHavePengirimanDone = $this->items()->get()->every(function ($item) {
            return $item->qty_ready_to_ship <= 0 && $item->total_shipped > 0;
        });

        if ($allItemsHavePengirimanDone && $this->items()->exists() && $this->status !== 'completed') {
            $this->forceFill(['status' => 'completed'])->saveQuietly();
        }
    }

    public function recalcTotals(): void
    {
        if (! $this->items()->exists()) {
            return;
        }

        $total = (float) $this->items()->sum('subtotal');
        if (((float) ($this->total_amount ?? 0)) !== $total) {
            $this->forceFill(['total_amount' => $total])->saveQuietly();
        }
    }
}
