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
        Schema::create('discount_rules', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('drogueria_id')->nullable();

            $table->unsignedInteger('min_qty_low')->default(0);
            $table->decimal('pct_low', 5, 2)->default(0);

            $table->unsignedInteger('min_qty_mid')->default(0);
            $table->decimal('pct_mid', 5, 2)->default(0);

            $table->unsignedInteger('min_qty_high')->default(0);
            $table->decimal('pct_high', 5, 2)->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['producto_id']);
            $table->index(['drogueria_id']);
            $table->unique(['producto_id', 'drogueria_id'], 'uq_discount_rules_producto_drogueria');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_rules');
    }
};
