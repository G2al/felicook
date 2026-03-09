<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AllergenPresenceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommercialProductAllergen extends Model
{
    use SoftDeletes;

    protected $table = 'commercial_product_allergen';

    protected $fillable = [
        'commercial_product_id',
        'allergen_id',
        'presence_type',
    ];

    protected function casts(): array
    {
        return [
            'presence_type' => AllergenPresenceType::class,
        ];
    }

    public function commercialProduct(): BelongsTo
    {
        return $this->belongsTo(CommercialProduct::class);
    }

    public function allergen(): BelongsTo
    {
        return $this->belongsTo(Allergen::class);
    }
}

