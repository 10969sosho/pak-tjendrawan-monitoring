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
        Schema::table('master_biayas', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('id')->constrained('products')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_biayas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
        });
    }
};
