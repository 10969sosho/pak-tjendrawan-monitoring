<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterBiaya extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function details()
    {
        return $this->hasMany(MasterBiayaDetail::class);
    }

    public function refreshEstimasiBiaya(): void
    {
        $total = $this->details()->sum('harga');
        $this->forceFill(['estimasi_biaya' => $total])->saveQuietly();
    }

    protected static function booted(): void
    {
        static::saved(function (self $model) {
            if ($model->wasChanged('estimasi_biaya')) {
                return;
            }

            if (! $model->relationLoaded('details')) {
                return;
            }

            $total = collect($model->details)->sum('harga');
            $model->forceFill(['estimasi_biaya' => $total])->saveQuietly();
        });
    }
}
