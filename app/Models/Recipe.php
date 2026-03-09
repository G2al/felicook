<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipe extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'category',
        'description',
        'portions',
        'total_weight',
        'yield_percentage',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'portions' => 'integer',
            'total_weight' => 'decimal:4',
            'yield_percentage' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function recipeIngredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'recipe_ingredients')
            ->withPivot(['id', 'quantity', 'unit_code', 'sort_order', 'deleted_at'])
            ->wherePivotNotNull('ingredient_id')
            ->wherePivotNull('deleted_at')
            ->withTimestamps();
    }

    public function commercialProducts(): BelongsToMany
    {
        return $this->belongsToMany(CommercialProduct::class, 'recipe_ingredients')
            ->withPivot(['id', 'quantity', 'unit_code', 'sort_order', 'deleted_at'])
            ->wherePivotNotNull('commercial_product_id')
            ->wherePivotNull('deleted_at')
            ->withTimestamps();
    }

    public function compoundIngredients(): HasMany
    {
        return $this->hasMany(Ingredient::class, 'recipe_id');
    }

    public function productionBatches(): HasMany
    {
        return $this->hasMany(ProductionBatch::class);
    }
}
