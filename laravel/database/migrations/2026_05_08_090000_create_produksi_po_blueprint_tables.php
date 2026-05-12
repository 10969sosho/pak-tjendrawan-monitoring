<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('pic')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('master_biayas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_produk');
            $table->string('kode_produk')->nullable();
            $table->decimal('estimasi_biaya', 15, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('master_biaya_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_biaya_id')->constrained('master_biayas')->cascadeOnDelete();
            $table->string('nama_biaya');
            $table->decimal('qty', 15, 3)->default(0);
            $table->string('satuan')->nullable();
            $table->decimal('harga', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('penawarans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_penawaran')->unique();
            $table->date('tanggal');
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('master_biaya_id')->nullable()->constrained('master_biayas')->nullOnDelete();
            $table->string('nama_produk');
            $table->decimal('qty', 15, 2)->default(0);
            $table->decimal('harga_penawaran', 15, 2)->default(0);
            $table->decimal('estimasi_biaya', 15, 2)->default(0);
            $table->decimal('estimasi_profit', 15, 2)->default(0);
            $table->string('status')->default('draft')->index();
            $table->timestamps();
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('id')->constrained('customers')->nullOnDelete();
            $table->foreignId('penawaran_id')->nullable()->after('customer_id')->constrained('penawarans')->nullOnDelete();
            $table->string('nama_produk')->nullable()->after('supplier_customer');
            $table->text('deskripsi_order')->nullable()->after('nama_produk');
            $table->decimal('qty_order', 15, 2)->nullable()->after('deskripsi_order');
            $table->decimal('harga_deal', 15, 2)->nullable()->after('qty_order');
            $table->date('deadline')->nullable()->after('harga_deal');
            $table->string('stage')->default('draft')->after('status')->index();
        });

        Schema::create('barang_masuks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->date('tanggal_masuk');
            $table->string('nama_barang');
            $table->string('jenis_barang')->nullable();
            $table->decimal('berat_masuk', 15, 3)->default(0);
            $table->string('satuan')->default('kg');
            $table->decimal('hasil_konversi_kg', 15, 3)->default(0);
            $table->text('catatan')->nullable();
            $table->string('status')->default('datang')->index();
            $table->string('qc_status')->default('pending')->index();
            $table->text('qc_catatan')->nullable();
            $table->timestamps();
        });

        Schema::table('productions', function (Blueprint $table) {
            $table->foreignId('purchase_order_id')->nullable()->after('id')->constrained('purchase_orders')->nullOnDelete();
            $table->foreignId('barang_masuk_id')->nullable()->after('purchase_order_id')->constrained('barang_masuks')->nullOnDelete();
            $table->decimal('berat_awal', 15, 3)->nullable()->after('date');
            $table->string('satuan_hasil')->default('pcs')->after('qty_produced');
            $table->text('catatan')->nullable()->after('status');
        });

        Schema::create('packings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_id')->constrained('productions')->cascadeOnDelete();
            $table->date('tanggal_packing');
            $table->decimal('total_pcs', 15, 2)->default(0);
            $table->decimal('isi_per_pack', 15, 2)->default(0);
            $table->decimal('total_pack', 15, 2)->default(0);
            $table->decimal('sisa_pcs', 15, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('pengirimans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->date('tanggal_kirim');
            $table->decimal('qty_kirim', 15, 2)->default(0);
            $table->string('penerima')->nullable();
            $table->text('catatan')->nullable();
            $table->string('status')->default('dikirim')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengirimans');
        Schema::dropIfExists('packings');

        Schema::table('productions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('purchase_order_id');
            $table->dropConstrainedForeignId('barang_masuk_id');
            $table->dropColumn(['berat_awal', 'satuan_hasil', 'catatan']);
        });

        Schema::dropIfExists('barang_masuks');

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_id');
            $table->dropConstrainedForeignId('penawaran_id');
            $table->dropColumn(['nama_produk', 'deskripsi_order', 'qty_order', 'harga_deal', 'deadline', 'stage']);
        });

        Schema::dropIfExists('penawarans');
        Schema::dropIfExists('master_biaya_details');
        Schema::dropIfExists('master_biayas');
        Schema::dropIfExists('customers');
    }
};

