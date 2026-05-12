<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Packing extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'tanggal_packing' => 'date',
        'total_pcs' => 'float',
        'isi_per_pack' => 'float',
        'total_pack' => 'float',
        'sisa_pcs' => 'float',
    ];

    public function production()
    {
        return $this->belongsTo(Production::class);
    }

    public function pengiriman()
    {
        return $this->hasOne(Pengiriman::class);
    }

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $totalPcs = max(0, (float) ($model->total_pcs ?? 0));
            $isiPerPack = max(0, (float) ($model->isi_per_pack ?? 0));

            $model->total_pcs = $totalPcs;
            $model->isi_per_pack = $isiPerPack;

            if ($isiPerPack <= 0) {
                $model->total_pack = 0;
                $model->sisa_pcs = $totalPcs;
                return;
            }

            $totalPack = (int) floor($totalPcs / $isiPerPack);
            $sisa = $totalPcs - ($totalPack * $isiPerPack);

            $model->total_pack = $totalPack;
            $model->sisa_pcs = $sisa;
        });

        static::saved(function (self $model) {
            $model->production?->purchaseOrderItem?->refreshStage();
            $model->production?->purchaseOrder?->refreshStage();
        });
    }
}
