<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockService
{
    public static function updateStock(Product $product, $qty, $type, $reference, $date)
    {
        DB::transaction(function () use ($product, $qty, $type, $reference, $date) {
            StockMovement::create([
                'product_id' => $product->id,
                'type' => $type,
                'qty' => $qty,
                'reference' => $reference,
                'date' => $date,
            ]);

            if ($type === 'in') {
                $product->increment('current_stock', $qty);
            } else {
                $product->decrement('current_stock', $qty);
            }
        });
    }
}
