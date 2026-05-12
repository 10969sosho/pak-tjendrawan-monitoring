<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productions', function (Blueprint $table) {
            $table->foreignId('purchase_order_item_id')
                ->nullable()
                ->after('purchase_order_id')
                ->constrained('purchase_order_items')
                ->nullOnDelete();
        });

        Schema::create('production_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_id')->constrained('productions')->cascadeOnDelete();
            $table->foreignId('barang_masuk_item_id')->constrained('barang_masuk_items')->cascadeOnDelete();
            $table->decimal('qty_used', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_materials');

        Schema::table('productions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('purchase_order_item_id');
        });
    }
};

