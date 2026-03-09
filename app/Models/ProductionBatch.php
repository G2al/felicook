<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionBatch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'recipe_id',
        'lot_code',
        'production_date',
        'expires_at',
        'produced_weight',
        'currency',
        'recipe_total_cost',
        'recipe_total_weight',
        'total_cost',
        'cost_per_kg',
        'public_price_per_kg',
        'allergens_snapshot',
        'nutrition_snapshot',
        'recipe_snapshot',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'production_date' => 'date',
            'expires_at' => 'date',
            'produced_weight' => 'decimal:4',
            'recipe_total_cost' => 'decimal:4',
            'recipe_total_weight' => 'decimal:4',
            'total_cost' => 'decimal:4',
            'cost_per_kg' => 'decimal:4',
            'public_price_per_kg' => 'decimal:4',
            'allergens_snapshot' => 'array',
            'nutrition_snapshot' => 'array',
            'recipe_snapshot' => 'array',
        ];
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class)->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductionBatchItem::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
