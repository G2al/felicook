<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommercialProduct extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'brand',
        'category',
        'ingredient_list',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function nutritionalValue(): HasOne
    {
        return $this->hasOne(CommercialProductNutritionalValue::class);
    }

    public function nutritionalValues(): HasMany
    {
        return $this->hasMany(CommercialProductNutritionalValue::class);
    }

    public function allergens(): BelongsToMany
    {
        return $this->belongsToMany(Allergen::class, 'commercial_product_allergen')
            ->withPivot(['id', 'presence_type', 'deleted_at'])
            ->wherePivotNull('deleted_at')
            ->withTimestamps();
    }

    public function commercialProductAllergens(): HasMany
    {
        return $this->hasMany(CommercialProductAllergen::class);
    }

    public function recipeIngredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    public function productionBatchItems(): HasMany
    {
        return $this->hasMany(ProductionBatchItem::class);
    }
}
