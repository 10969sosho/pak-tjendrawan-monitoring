<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pengirimans', function (Blueprint $table) {
            $table->foreignId('packing_id')->nullable()->constrained()->after('purchase_order_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengirimans', function (Blueprint $table) {
            $table->dropForeign(['packing_id']);
            $table->dropColumn('packing_id');
        });
    }
};
