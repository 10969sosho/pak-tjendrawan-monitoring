<?php

namespace App\Observers;

use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class PurchaseOrderObserver
{
    public function updated(PurchaseOrder $purchaseOrder): void
    {
        if ($purchaseOrder->isDirty('status') && $purchaseOrder->status === 'completed') {
            DB::transaction(function () use ($purchaseOrder) {
                foreach ($purchaseOrder->items as $item) {
                    // Update Product Stock
                    $product = $item->product;
                    $product->increment('current_stock', $item->qty);

                    // Record Movement
                    StockMovement::create([
                        'product_id' => $item->product_id,
                        'type' => 'in',
                        'qty' => $item->qty,
                        'reference' => $purchaseOrder->number,
                        'date' => now(),
                    ]);
                }
            });
        }
    }
}
