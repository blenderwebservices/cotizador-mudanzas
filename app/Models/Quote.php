<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'distancia_km' => 'decimal:2',
        'tiempo_traslado_horas' => 'decimal:2',
        'volumen_total_m3' => 'decimal:3',
        'costo_empaque_total' => 'decimal:2',
        'combustible_l' => 'decimal:2',
        'costo_combustible' => 'decimal:2',
        'tiempo_empaque_total_min' => 'integer',
        'tiempo_carga_horas' => 'decimal:2',
        'material_empaque_costo' => 'decimal:2',
        'comida_trabajadores_costo' => 'decimal:2',
        'salarios_costo' => 'decimal:2',
        'gastos_totales' => 'decimal:2',
        'ganancia_estimada' => 'decimal:2',
        'precio_sugerido' => 'decimal:2',
        'detalles_json' => 'array',
        'personas_sugeridas' => 'integer',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function vehiculoSugerido(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehiculo_sugerido_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }
}
