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
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            // Client Contact Info
            $table->string('nombre_cliente');
            $table->string('email_cliente');
            $table->string('telefono_cliente')->nullable();
            $table->text('origen');
            $table->text('destino');
            
            // Core Metrics & Inputs
            $table->decimal('distancia_km', 8, 2)->default(0);
            $table->decimal('tiempo_traslado_horas', 5, 2)->default(0);
            $table->decimal('volumen_total_m3', 8, 3)->default(0);
            $table->decimal('costo_empaque_total', 10, 2)->default(0);
            
            // Suggestion variables
            $table->foreignId('vehiculo_sugerido_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->integer('personas_sugeridas')->default(2);
            
            // Financial Breakdown
            $table->decimal('combustible_l', 8, 2)->default(0);
            $table->decimal('costo_combustible', 10, 2)->default(0);
            $table->integer('tiempo_empaque_total_min')->default(0);
            $table->decimal('tiempo_carga_horas', 5, 2)->default(0);
            $table->decimal('material_empaque_costo', 10, 2)->default(0);
            $table->decimal('comida_trabajadores_costo', 10, 2)->default(0);
            $table->decimal('salarios_costo', 10, 2)->default(0);
            
            // Summary Totals
            $table->decimal('gastos_totales', 10, 2)->default(0);
            $table->decimal('ganancia_estimada', 10, 2)->default(0);
            $table->decimal('precio_sugerido', 10, 2)->default(0);
            
            // Additional Payload & Admin assignment
            $table->json('detalles_json')->nullable();
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
