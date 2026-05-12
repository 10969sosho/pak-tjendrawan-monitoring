<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function boms()
    {
        return $this->hasMany(BillOfMaterial::class, 'product_id');
    }

    public function materials()
    {
        return $this->hasMany(BillOfMaterial::class, 'material_id');
    }
}
