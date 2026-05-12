<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penawaran extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function masterBiaya()
    {
        return $this->belongsTo(MasterBiaya::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $estimasiBiaya = $model->masterBiaya?->estimasi_biaya ?? $model->estimasi_biaya ?? 0;
            $hargaPenawaran = (float) ($model->harga_penawaran ?? 0);

            $model->estimasi_biaya = (float) $estimasiBiaya;
            $model->estimasi_profit = $hargaPenawaran - (float) $estimasiBiaya;
        });
    }
}

