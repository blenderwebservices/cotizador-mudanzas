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
        Schema::table('quotes', function (Blueprint $table) {
            // Drivers logísticos de Origen
            $table->integer('pisos_origen')->default(1)->after('origen');
            $table->integer('distancia_caminata_origen_m')->default(10)->after('pisos_origen');

            // Drivers logísticos de Destino
            $table->integer('pisos_destino')->default(1)->after('destino');
            $table->boolean('ascensor_destino')->default(true)->after('pisos_destino');
            $table->integer('distancia_caminata_destino_m')->default(10)->after('ascensor_destino');

            // Desglose de costos de las 5 actividades ABC
            $table->decimal('costo_actividad_comercial', 10, 2)->default(0)->after('personas_sugeridas');
            $table->decimal('costo_actividad_embalaje', 10, 2)->default(0)->after('costo_actividad_comercial');
            $table->decimal('costo_actividad_carga', 10, 2)->default(0)->after('costo_actividad_embalaje');
            $table->decimal('costo_actividad_transporte', 10, 2)->default(0)->after('costo_actividad_carga');
            $table->decimal('costo_actividad_descarga', 10, 2)->default(0)->after('costo_actividad_transporte');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn([
                'pisos_origen',
                'distancia_caminata_origen_m',
                'pisos_destino',
                'ascensor_destino',
                'distancia_caminata_destino_m',
                'costo_actividad_comercial',
                'costo_actividad_embalaje',
                'costo_actividad_carga',
                'costo_actividad_transporte',
                'costo_actividad_descarga'
            ]);
        });
    }
};
