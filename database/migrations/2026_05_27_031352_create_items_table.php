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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->integer('cantidad')->default(1);
            $table->decimal('costo_empaque', 10, 2)->default(0);
            $table->integer('tiempo_empaque')->default(0); // in minutes
            $table->decimal('tamano_volumetrico', 8, 3)->default(0); // in cubic meters (m³)
            $table->string('nivel_riesgo')->default('bajo'); // bajo, medio, alto
            $table->boolean('requiere_desarmarse')->default(false);
            $table->boolean('activo')->default(true);
            $table->boolean('permite_detalles_opcionales')->default(false); // for tv & fridge
            $table->string('icon')->nullable(); // emoji icon (e.g. 🛏️, 📺, etc.)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
