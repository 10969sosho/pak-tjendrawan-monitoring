<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangMasuk extends Model
{
    use HasFactory;

    protected $table = 'barang_masuks';

    protected $guarded = [];

    protected $casts = [
        'tanggal_masuk' => 'date',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function productions()
    {
        return $this->hasMany(Production::class);
    }

    public function items()
    {
        return $this->hasMany(BarangMasukItem::class);
    }

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            if ($model->berat_masuk === null || $model->satuan === null) {
                $model->hasil_konversi_kg = null;
                return;
            }

            $berat = (float) $model->berat_masuk;
            $satuan = strtolower((string) $model->satuan);

            $model->hasil_konversi_kg = $satuan === 'ton'
                ? $berat * 1000
                : $berat;
        });

        static::saved(function (self $model) {
            $model->purchaseOrder?->refreshStage();
        });
    }
}
