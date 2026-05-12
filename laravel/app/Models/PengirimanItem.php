<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengirimanItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'qty_kirim' => 'float',
    ];

    public function pengiriman()
    {
        return $this->belongsTo(Pengiriman::class);
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }
}
