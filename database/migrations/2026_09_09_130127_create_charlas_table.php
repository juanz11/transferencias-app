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
        Schema::create('charlas', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->integer('cliente_id');
            $table->string('direccion')->nullable();
            $table->integer('cantidad_charlas');
            $table->integer('participantes');
            $table->integer('visitador_id')->nullable();
            $table->timestamps();

            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->foreign('visitador_id')->references('id')->on('visitadores')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('charlas');
    }
};
