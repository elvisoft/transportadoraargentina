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
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();
            $table->string('patente')->unique();
            $table->string('marca');
            $table->string('modelo');
            $table->smallInteger('anio')->nullable();
            $table->string('tipo');
            $table->foreignId('chofer_id')->nullable()->constrained('choferes')->nullOnDelete();
            $table->date('rto_vencimiento')->nullable();
            $table->string('seguro_compania')->nullable();
            $table->date('seguro_vencimiento')->nullable();
            $table->string('estado')->default('activo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};
