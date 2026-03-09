<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'vat_number',
        'email',
        'phone',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function ingredientSuppliers(): HasMany
    {
        return $this->hasMany(IngredientSupplier::class);
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'ingredient_supplier')
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
}
