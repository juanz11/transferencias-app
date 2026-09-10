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
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn('direccion');
        });

        Schema::table('charlas', function (Blueprint $table) {
            $table->renameColumn('direccion', 'zona');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('direccion')->nullable()->after('zona');
        });

        Schema::table('charlas', function (Blueprint $table) {
            $table->renameColumn('zona', 'direccion');
        });
    }
};
