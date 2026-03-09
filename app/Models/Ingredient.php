<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'label_name',
        'internal_code',
        'category',
        'base_unit_code',
        'is_frozen',
        'is_blast_chilled',
        'is_organic',
        'is_active',
        'notes',
        'recipe_id',
    ];

    protected function casts(): array
    {
        return [
            'is_frozen' => 'boolean',
            'is_blast_chilled' => 'boolean',
            'is_organic' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit_code', 'code');
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function nutritionalValue(): HasOne
    {
        return $this->hasOne(NutritionalValue::class);
    }

    public function nutritionalValues(): HasMany
    {
        return $this->hasMany(NutritionalValue::class);
    }

    public function ingredientSuppliers(): HasMany
    {
        return $this->hasMany(IngredientSupplier::class);
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'ingredient_supplier')
            ->withPivot([
                'id',
                'price',
                'currency',
                'price_type',
                'unit_code',
                'pack_quantity',
                'pack_unit_code',
                'valid_from',
                'valid_to',
                'deleted_at',
            ])
            ->wherePivotNull('deleted_at')
            ->withTimestamps();
    }

    public function allergens(): BelongsToMany
    {
        return $this->belongsToMany(Allergen::class, 'allergen_ingredient')
            ->withPivot(['id', 'presence_type', 'deleted_at'])
            ->wherePivotNull('deleted_at')
            ->withTimestamps();
    }

    public function ingredientAllergens(): HasMany
    {
        return $this->hasMany(IngredientAllergen::class);
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
