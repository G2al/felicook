<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AllergenPresenceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class IngredientAllergen extends Model
{
    use SoftDeletes;

    protected $table = 'allergen_ingredient';

    protected $fillable = [
        'ingredient_id',
        'allergen_id',
        'presence_type',
    ];

    protected function casts(): array
    {
        return [
            'presence_type' => AllergenPresenceType::class,
        ];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function allergen(): BelongsTo
    {
        return $this->belongsTo(Allergen::class);
    }
}
