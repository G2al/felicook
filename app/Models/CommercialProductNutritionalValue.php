<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommercialProductNutritionalValue extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'commercial_product_id',
        'energy_kj',
        'energy_kcal',
        'fat',
        'saturated_fat',
        'mono_fat',
        'poly_fat',
        'carbs',
        'sugars',
        'polyols',
        'erythritol',
        'fiber',
        'protein',
        'salt',
        'alcohol',
        'water',
        'edible_part_percentage',
    ];

    protected function casts(): array
    {
        return [
            'energy_kj' => 'decimal:4',
            'energy_kcal' => 'decimal:4',
            'fat' => 'decimal:4',
            'saturated_fat' => 'decimal:4',
            'mono_fat' => 'decimal:4',
            'poly_fat' => 'decimal:4',
            'carbs' => 'decimal:4',
            'sugars' => 'decimal:4',
            'polyols' => 'decimal:4',
            'erythritol' => 'decimal:4',
            'fiber' => 'decimal:4',
            'protein' => 'decimal:4',
            'salt' => 'decimal:4',
            'alcohol' => 'decimal:4',
            'water' => 'decimal:4',
            'edible_part_percentage' => 'decimal:2',
        ];
    }

    public function commercialProduct(): BelongsTo
    {
        return $this->belongsTo(CommercialProduct::class);
    }
}

