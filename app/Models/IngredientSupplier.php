<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\IngredientSupplierPriceType;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class IngredientSupplier extends Model
{
    use SoftDeletes;

    protected $table = 'ingredient_supplier';

    protected $fillable = [
        'ingredient_id',
        'supplier_id',
        'price',
        'currency',
        'price_type',
        'unit_code',
        'pack_quantity',
        'pack_unit_code',
        'valid_from',
        'valid_to',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:4',
            'price_type' => IngredientSupplierPriceType::class,
            'pack_quantity' => 'decimal:4',
            'valid_from' => 'date',
            'valid_to' => 'date',
        ];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_code', 'code');
    }

    public function packUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'pack_unit_code', 'code');
    }

    public function scopeValidOn(Builder $query, CarbonInterface $date): Builder
    {
        return $query
            ->where(function (Builder $builder) use ($date): void {
                $builder
                    ->whereNull('valid_from')
                    ->orWhereDate('valid_from', '<=', $date->toDateString());
            })
            ->where(function (Builder $builder) use ($date): void {
                $builder
                    ->whereNull('valid_to')
                    ->orWhereDate('valid_to', '>=', $date->toDateString());
            });
    }
}
