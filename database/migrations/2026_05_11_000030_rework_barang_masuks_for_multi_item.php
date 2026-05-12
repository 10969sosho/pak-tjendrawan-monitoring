<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang_masuks', function (Blueprint $table) {
            $table->string('nama_barang')->nullable()->change();
            $table->string('jenis_barang')->nullable()->change();
            $table->decimal('berat_masuk', 15, 3)->nullable()->change();
            $table->string('satuan')->nullable()->change();
            $table->decimal('hasil_konversi_kg', 15, 3)->nullable()->change();
            $table->string('status')->nullable()->change();
            $table->string('qc_status')->nullable()->change();
        });

        Schema::create('barang_masuk_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_masuk_id')->constrained('barang_masuks')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('qty', 15, 2)->default(0);
            $table->string('unit')->nullable();
            $table->string('qc_status')->default('pending')->index();
            $table->text('qc_catatan')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_masuk_items');

        Schema::table('barang_masuks', function (Blueprint $table) {
            $table->string('nama_barang')->nullable(false)->change();
            $table->string('jenis_barang')->nullable(true)->change();
            $table->decimal('berat_masuk', 15, 3)->nullable(false)->default(0)->change();
            $table->string('satuan')->nullable(false)->default('kg')->change();
            $table->decimal('hasil_konversi_kg', 15, 3)->nullable(false)->default(0)->change();
            $table->string('status')->nullable(false)->default('datang')->change();
            $table->string('qc_status')->nullable(false)->default('pending')->change();
        });
    }
};

