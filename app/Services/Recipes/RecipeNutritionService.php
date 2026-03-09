<?php

declare(strict_types=1);

namespace App\Services\Recipes;

use App\Models\CommercialProduct;
use App\Models\CommercialProductNutritionalValue;
use App\Models\Ingredient;
use App\Models\NutritionalValue;
use App\Models\Recipe;
use App\Services\Units\UnitConversionService;
use RuntimeException;

class RecipeNutritionService
{
    protected const NUTRIENT_FIELDS = [
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

    public function __construct(
        protected UnitConversionService $unitConversionService,
    ) {}

    public function calculate(Recipe $recipe, array $visitedRecipeIds = []): array
    {
        if ($recipe->id !== null) {
            if (in_array($recipe->id, $visitedRecipeIds, true)) {
                throw new RuntimeException("Riferimento ricorsivo rilevato per la ricetta [{$recipe->id}].");
            }

            $visitedRecipeIds[] = $recipe->id;
        }

        $recipe->loadMissing([
            'recipeIngredients' => fn ($query) => $query->orderBy('sort_order'),
            'recipeIngredients.ingredient.recipe',
            'recipeIngredients.ingredient.nutritionalValue',
            'recipeIngredients.commercialProduct.nutritionalValue',
        ]);

        $totals = $this->emptyNutrition();
        $rawTotalWeight = 0.0;

        foreach ($recipe->recipeIngredients as $recipeIngredient) {
            $ingredient = $recipeIngredient->ingredient;
            $commercialProduct = $recipeIngredient->commercialProduct;
            $quantity = (float) $recipeIngredient->quantity;

            if ($quantity <= 0) {
                continue;
            }

            if ($ingredient instanceof Ingredient) {
                $unitCode = (string) ($recipeIngredient->unit_code ?: $ingredient->base_unit_code);
                $quantityInGrams = $this->resolveQuantityInGrams($quantity, $unitCode, $ingredient);
                $profile = $this->resolveIngredientProfilePer100g($ingredient, $visitedRecipeIds);

                if ($profile === null || $quantityInGrams <= 0) {
                    continue;
                }

                foreach (self::NUTRIENT_FIELDS as $field) {
                    $totals[$field] += (($profile[$field] ?? 0.0) * $quantityInGrams) / 100;
                }

                $rawTotalWeight += $quantityInGrams;

                continue;
            }

            if ($commercialProduct instanceof CommercialProduct) {
                $unitCode = (string) ($recipeIngredient->unit_code ?: 'kg');
                $quantityInGrams = $this->resolveCommercialProductQuantityInGrams($quantity, $unitCode);
                $profile = $this->resolveCommercialProductProfilePer100g($commercialProduct);

                if ($profile === null || $quantityInGrams <= 0) {
                    continue;
                }

                foreach (self::NUTRIENT_FIELDS as $field) {
                    $totals[$field] += (($profile[$field] ?? 0.0) * $quantityInGrams) / 100;
                }

                $rawTotalWeight += $quantityInGrams;
            }
        }

        $yieldFactor = $this->normalizeYieldFactor((float) ($recipe->yield_percentage ?? 100));
        $finalWeight = $rawTotalWeight * $yieldFactor;
        $portions = (int) ($recipe->portions ?? 0);
        $per100g = $this->emptyNutrition();
        $perPortion = $this->emptyNutrition();

        foreach (self::NUTRIENT_FIELDS as $field) {
            $per100g[$field] = $finalWeight > 0 ? ($totals[$field] / $finalWeight) * 100 : 0.0;
            $perPortion[$field] = $portions > 0 ? $totals[$field] / $portions : 0.0;
        }

        return [
            'totals' => $totals,
            'per_100g' => $per100g,
            'per_portion' => $perPortion,
            'raw_total_weight' => $rawTotalWeight,
            'total_weight' => $finalWeight,
            'yield_percentage' => (float) ($recipe->yield_percentage ?? 100),
            'portions' => $portions,
        ];
    }

    protected function resolveIngredientProfilePer100g(Ingredient $ingredient, array $visitedRecipeIds): ?array
    {
        if ($ingredient->recipe_id !== null && $ingredient->recipe !== null) {
            $compoundRecipeNutrition = $this->calculate($ingredient->recipe, $visitedRecipeIds);

            return $compoundRecipeNutrition['per_100g'];
        }

        $nutritionalValue = $ingredient->nutritionalValue;

        if (! $nutritionalValue instanceof NutritionalValue) {
            return null;
        }

        $profile = $this->emptyNutrition();

        foreach (self::NUTRIENT_FIELDS as $field) {
            $profile[$field] = (float) ($nutritionalValue->{$field} ?? 0);
        }

        return $profile;
    }

    protected function resolveCommercialProductProfilePer100g(CommercialProduct $commercialProduct): ?array
    {
        $nutritionalValue = $commercialProduct->nutritionalValue;

        if (! $nutritionalValue instanceof CommercialProductNutritionalValue) {
            return null;
        }

        $profile = $this->emptyNutrition();

        foreach (self::NUTRIENT_FIELDS as $field) {
            $profile[$field] = (float) ($nutritionalValue->{$field} ?? 0);
        }

        return $profile;
    }

    protected function resolveQuantityInGrams(float $quantity, string $unitCode, Ingredient $ingredient): float
    {
        $direct = $this->unitConversionService->tryConvert($quantity, $unitCode, 'g');

        if ($direct !== null) {
            return $direct;
        }

        $inBaseUnit = $this->unitConversionService->tryConvert(
            $quantity,
            $unitCode,
            (string) $ingredient->base_unit_code,
        );

        if ($inBaseUnit === null) {
            return 0.0;
        }

        return $this->unitConversionService->tryConvert(
            $inBaseUnit,
            (string) $ingredient->base_unit_code,
            'g',
        ) ?? 0.0;
    }

    protected function resolveCommercialProductQuantityInGrams(float $quantity, string $unitCode): float
    {
        return $this->unitConversionService->tryConvert($quantity, $unitCode, 'g') ?? 0.0;
    }

    protected function normalizeYieldFactor(float $yieldPercentage): float
    {
        return $yieldPercentage > 0 ? $yieldPercentage / 100 : 1.0;
    }

    protected function emptyNutrition(): array
    {
        $values = [];

        foreach (self::NUTRIENT_FIELDS as $field) {
            $values[$field] = 0.0;
        }

        return $values;
    }
}
