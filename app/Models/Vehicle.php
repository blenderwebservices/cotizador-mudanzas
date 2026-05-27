<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'capacidad_m3',
        'consumo_kml',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'capacidad_m3' => 'decimal:2',
        'consumo_kml' => 'decimal:2',
    ];
}
