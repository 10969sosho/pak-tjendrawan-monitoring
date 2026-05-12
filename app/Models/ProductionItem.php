<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionItem extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function production()
    {
        return $this->belongsTo(Production::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class); // Material
    }
}
