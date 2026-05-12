<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengirimans', function (Blueprint $table) {
            $table->foreignId('purchase_order_item_id')
                ->nullable()
                ->after('purchase_order_id')
                ->constrained('purchase_order_items')
                ->nullOnDelete();

            $table->string('tujuan_pengiriman')->nullable()->after('penerima');
            $table->text('detail_pengiriman')->nullable()->after('tujuan_pengiriman');

            $table->string('status')->default('siap_kirim')->change();
        });
    }

    public function down(): void
    {
        Schema::table('pengirimans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('purchase_order_item_id');
            $table->dropColumn(['tujuan_pengiriman', 'detail_pengiriman']);
            $table->string('status')->default('dikirim')->change();
        });
    }
};

