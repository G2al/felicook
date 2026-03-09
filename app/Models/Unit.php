<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UnitDimension;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $primaryKey = 'code';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'dimension',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'dimension' => UnitDimension::class,
            'is_active' => 'boolean',
        ];
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class, 'base_unit_code', 'code');
    }

    public function ingredientSupplierUnits(): HasMany
    {
        return $this->hasMany(IngredientSupplier::class, 'unit_code', 'code');
    }

    public function ingredientSupplierPackUnits(): HasMany
    {
        return $this->hasMany(IngredientSupplier::class, 'pack_unit_code', 'code');
    }

    public function recipeIngredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class, 'unit_code', 'code');
    }

    public function fromConversions(): HasMany
    {
        return $this->hasMany(UnitConversion::class, 'from_unit_code', 'code');
    }

    public function toConversions(): HasMany
    {
        return $this->hasMany(UnitConversion::class, 'to_unit_code', 'code');
    }

    public function productionBatchItems(): HasMany
    {
        return $this->hasMany(ProductionBatchItem::class, 'unit_code', 'code');
    }
}
