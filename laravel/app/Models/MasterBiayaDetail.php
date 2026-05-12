<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterBiayaDetail extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function masterBiaya()
    {
        return $this->belongsTo(MasterBiaya::class);
    }

    protected static function booted(): void
    {
        static::saved(function (self $model) {
            $model->masterBiaya?->refreshEstimasiBiaya();
        });

        static::deleted(function (self $model) {
            $model->masterBiaya?->refreshEstimasiBiaya();
        });
    }
}

