<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProductionItemSourceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionBatchItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'production_batch_id',
        'ingredient_id',
        'commercial_product_id',
        'source_type',
        'name_snapshot',
        'quantity',
        'unit_code',
        'quantity_in_grams',
        'lot_code',
        'expires_at',
        'sort_order',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'source_type' => ProductionItemSourceType::class,
            'quantity' => 'decimal:4',
            'quantity_in_grams' => 'decimal:4',
            'expires_at' => 'date',
            'sort_order' => 'integer',
            'meta' => 'array',
        ];
    }

    public function productionBatch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class);
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
}
