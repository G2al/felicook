<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Allergen extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'code',
    ];

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'allergen_ingredient')
            ->withPivot(['id', 'presence_type', 'deleted_at'])
            ->wherePivotNull('deleted_at')
            ->withTimestamps();
    }

    public function ingredientAllergens(): HasMany
    {
        return $this->hasMany(IngredientAllergen::class);
    }
}
