<?php

namespace App\Observers;

use App\Models\Production;
use App\Models\StockMovement;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductionObserver
{
    public function updated(Production $production): void
    {
        if ($production->isDirty('status') && $production->status === 'completed') {
            DB::transaction(function () use ($production) {
                // 1. Decrease Materials Stock
                foreach ($production->materials as $material) {
                    $product = $material->product;
                    if ($product) {
                        $product->decrement('current_stock', $material->qty_used);

                        StockMovement::create([
                            'product_id' => $material->product_id,
                            'type' => 'out',
                            'qty' => $material->qty_used,
                            'reference' => $production->number,
                            'date' => now(),
                        ]);
                    }
                }

                // 2. Increase Finished Product Stock
                $finishedProduct = $production->purchaseOrderItem?->product;
                if ($finishedProduct) {
                    $finishedProduct->increment('current_stock', $production->qty_produced);

                    StockMovement::create([
                        'product_id' => $finishedProduct->id,
                        'type' => 'in',
                        'qty' => $production->qty_produced,
                        'reference' => $production->number,
                        'date' => now(),
                    ]);
                }
            });
        }
    }
}
