<?php

declare(strict_types=1);

namespace App\Services\Ingredients;

use App\Enums\IngredientSupplierPriceType;
use App\Models\Ingredient;
use App\Models\IngredientSupplier;
use App\Services\Units\UnitConversionService;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use RuntimeException;

class IngredientPricingService
{
    public function __construct(
        protected UnitConversionService $unitConversionService,
    ) {}

    public function resolvePreferredPrice(Ingredient $ingredient, ?CarbonInterface $referenceDate = null): ?IngredientSupplier
    {
        $referenceDate ??= Carbon::now();

        $validQuery = $ingredient->ingredientSuppliers()
            ->with('supplier')
            ->validOn($referenceDate)
            ->orderByDesc('valid_from')
            ->orderByDesc('id');

        $preferredActivePrice = (clone $validQuery)
            ->whereHas('supplier', fn (Builder $query): Builder => $query->where('is_active', true))
            ->first();

        if ($preferredActivePrice !== null) {
            return $preferredActivePrice;
        }

        $latestValidPrice = (clone $validQuery)->first();

        if ($latestValidPrice !== null) {
            return $latestValidPrice;
        }

        return $ingredient->ingredientSuppliers()
            ->with('supplier')
            ->orderByDesc('valid_from')
            ->orderByDesc('id')
            ->first();
    }

    public function calculateCostForBaseQuantity(
        Ingredient $ingredient,
        float $quantityInBaseUnit,
        ?CarbonInterface $referenceDate = null,
    ): array {
        $pricing = $this->resolvePreferredPrice($ingredient, $referenceDate);

        if ($pricing === null) {
            return [
                'cost' => 0.0,
                'currency' => 'EUR',
                'pricing' => null,
            ];
        }

        $unitCode = (string) $ingredient->base_unit_code;
        $price = (float) $pricing->price;
        $cost = 0.0;
        $priceType = $pricing->price_type instanceof IngredientSupplierPriceType
            ? $pricing->price_type
            : IngredientSupplierPriceType::from((string) $pricing->price_type);

        if ($priceType === IngredientSupplierPriceType::PerUnit) {
            if (blank($pricing->unit_code)) {
                throw new RuntimeException("Il codice unità è obbligatorio per il prezzo per unità dell'ingrediente [{$ingredient->id}].");
            }

            $quantityInPricingUnit = $this->unitConversionService->convert(
                $quantityInBaseUnit,
                $unitCode,
                (string) $pricing->unit_code,
            );

            $cost = $quantityInPricingUnit * $price;
        }

        if ($priceType === IngredientSupplierPriceType::PerPack) {
            if (blank($pricing->pack_unit_code) || ((float) $pricing->pack_quantity <= 0)) {
                throw new RuntimeException("Quantità e unità confezione sono obbligatorie per il prezzo per confezione dell'ingrediente [{$ingredient->id}].");
            }

            $packQuantityInBaseUnit = $this->unitConversionService->convert(
                (float) $pricing->pack_quantity,
                (string) $pricing->pack_unit_code,
                $unitCode,
            );

            if ($packQuantityInBaseUnit <= 0) {
                throw new RuntimeException("La conversione della quantità confezione deve essere maggiore di zero per l'ingrediente [{$ingredient->id}].");
            }

            $cost = ($quantityInBaseUnit / $packQuantityInBaseUnit) * $price;
        }

        return [
            'cost' => $cost,
            'currency' => (string) $pricing->currency,
            'pricing' => $pricing,
        ];
    }
}
