<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\ExpenseCategory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Production;
use App\Models\ProductionItem;
use App\Models\BillOfMaterial;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // Categories
        $catBahan = Category::create(['name' => 'Bahan Baku', 'slug' => 'bahan-baku']);
        $catJadi = Category::create(['name' => 'Barang Jadi', 'slug' => 'barang-jadi']);

        // Products (Raw Materials)
        $terigu = Product::create([
            'category_id' => $catBahan->id,
            'name' => 'Tepung Terigu',
            'sku' => 'RM-001',
            'unit' => 'kg',
            'current_stock' => 100,
            'min_stock' => 20,
            'price' => 12000
        ]);

        $gula = Product::create([
            'category_id' => $catBahan->id,
            'name' => 'Gula Pasir',
            'sku' => 'RM-002',
            'unit' => 'kg',
            'current_stock' => 50,
            'min_stock' => 10,
            'price' => 15000
        ]);

        // Products (Finished Goods)
        $roti = Product::create([
            'category_id' => $catJadi->id,
            'name' => 'Roti Tawar',
            'sku' => 'FG-001',
            'unit' => 'pcs',
            'current_stock' => 0,
            'min_stock' => 50,
            'price' => 15000
        ]);

        // BOM
        BillOfMaterial::create([
            'product_id' => $roti->id,
            'material_id' => $terigu->id,
            'qty_needed' => 0.5
        ]);
        BillOfMaterial::create([
            'product_id' => $roti->id,
            'material_id' => $gula->id,
            'qty_needed' => 0.1
        ]);

        // Expense Categories
        ExpenseCategory::create(['name' => 'Listrik']);
        ExpenseCategory::create(['name' => 'Gaji Staff']);
        ExpenseCategory::create(['name' => 'Transportasi']);

        // Purchase Order
        $po = PurchaseOrder::create([
            'number' => 'PO-20260428001',
            'date' => now(),
            'supplier_customer' => 'Supplier Terigu Jaya',
            'status' => 'draft',
            'total_amount' => 1200000
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $terigu->id,
            'qty' => 100,
            'price' => 12000,
            'subtotal' => 1200000
        ]);
    }
}
