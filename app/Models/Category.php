<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'categoria', 'name');
    }

    protected static function booted()
    {
        static::updating(function (Category $category) {
            if ($category->isDirty('name')) {
                $oldName = $category->getOriginal('name');
                $newName = $category->name;
                Item::where('categoria', $oldName)->update(['categoria' => $newName]);
            }
        });
    }
}
