<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'categoria',
        'grupo_categoria',
        'cantidad',
        'costo_empaque',
        'tiempo_empaque',
        'tamano_volumetrico',
        'nivel_riesgo',
        'requiere_desarmarse',
        'activo',
        'permite_detalles_opcionales',
        'icon',
    ];

    protected $casts = [
        'requiere_desarmarse' => 'boolean',
        'activo' => 'boolean',
        'permite_detalles_opcionales' => 'boolean',
        'costo_empaque' => 'decimal:2',
        'tamano_volumetrico' => 'decimal:3',
        'cantidad' => 'integer',
        'tiempo_empaque' => 'integer',
    ];

    public function quoteItems(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'categoria', 'name');
    }

    public function groupCategory(): BelongsTo
    {
        return $this->belongsTo(GroupCategory::class, 'grupo_categoria', 'name');
    }
}
