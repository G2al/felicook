<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProductionItemSourceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecipeIngredient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'recipe_id',
        'ingredient_id',
        'commercial_product_id',
        'quantity',
        'unit_code',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'sort_order' => 'integer',
        ];
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class)->withTrashed();
    }

    public function commercialProduct(): BelongsTo
    {
        return $this->belongsTo(CommercialProduct::class)->withTrashed();
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_code', 'code');
    }

    public function resolveSourceType(): ProductionItemSourceType
    {
        if ($this->commercial_product_id !== null) {
            return ProductionItemSourceType::CommercialProduct;
        }

        return ProductionItemSourceType::Ingredient;
    }
}
