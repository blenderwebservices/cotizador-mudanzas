<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'grupo_categoria', 'name');
    }

    protected static function booted()
    {
        static::updating(function (GroupCategory $group) {
            if ($group->isDirty('name')) {
                $oldName = $group->getOriginal('name');
                $newName = $group->name;
                Item::where('grupo_categoria', $oldName)->update(['grupo_categoria' => $newName]);
            }
        });
    }
}
