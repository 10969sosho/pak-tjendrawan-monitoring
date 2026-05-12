<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengiriman extends Model
{
    use HasFactory;

    protected $table = 'pengirimans';

    protected $guarded = [];

    protected $casts = [
        'tanggal_kirim' => 'date',
        'qty_kirim' => 'float',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function packing()
    {
        return $this->belongsTo(Packing::class);
    }

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $model->qty_kirim = max(0, (float) ($model->qty_kirim ?? 0));
        });

        static::saved(function (self $model) {
            $model->purchaseOrderItem?->refreshStage();
            $model->purchaseOrder?->refreshStage();
        });
    }
}
