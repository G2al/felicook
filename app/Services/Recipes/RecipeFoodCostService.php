<?php

declare(strict_types=1);

namespace App\Services\Recipes;

use App\Enums\UnitDimension;
use App\Models\CommercialProduct;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Services\Ingredients\IngredientPricingService;
use App\Services\Units\UnitConversionService;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use RuntimeException;

class RecipeFoodCostService
{
    public function __construct(
        protected UnitConversionService $unitConversionService,
        protected IngredientPricingService $ingredientPricingService,
    ) {}

    public function calculate(
        Recipe $recipe,
        ?CarbonInterface $referenceDate = null,
        array $visitedRecipeIds = [],
    ): array {
        $referenceDate ??= Carbon::now();

        if ($recipe->id !== null) {
            if (in_array($recipe->id, $visitedRecipeIds, true)) {
                throw new RuntimeException("Riferimento ricorsivo rilevato per la ricetta [{$recipe->id}].");
            }

            $visitedRecipeIds[] = $recipe->id;
        }

        $recipe->loadMissing([
            'recipeIngredients' => fn ($query) => $query->orderBy('sort_order'),
            'recipeIngredients.ingredient.baseUnit',
            'recipeIngredients.ingredient.recipe',
            'recipeIngredients.ingredient.ingredientSuppliers.supplier',
            'recipeIngredients.commercialProduct',
        ]);

        $entries = [];

        foreach ($recipe->recipeIngredients as $recipeIngredient) {
            $quantity = (float) $recipeIngredient->quantity;

            if ($quantity <= 0) {
                continue;
            }

            if ($recipeIngredient->ingredient !== null) {
                $entries[] = [
                    'ingredient' => $recipeIngredient->ingredient,
                    'quantity' => $quantity,
                    'unit_code' => (string) ($recipeIngredient->unit_code ?: $recipeIngredient->ingredient->base_unit_code),
                ];
            }

            if ($recipeIngredient->commercialProduct !== null) {
                $entries[] = [
                    'commercial_product' => $recipeIngredient->commercialProduct,
                    'quantity' => $quantity,
                    'unit_code' => (string) $recipeIngredient->unit_code,
                ];
            }
        }

        return $this->calculateEntries(
            $entries,
            (float) ($recipe->yield_percentage ?? 100),
            (int) ($recipe->portions ?? 0),
            $referenceDate,
            $visitedRecipeIds,
        );
    }

    public function calculateFromFormState(
        array $recipeIngredientsState,
        float $yieldPercentage = 100,
        int $portions = 1,
        ?CarbonInterface $referenceDate = null,
        array $visitedRecipeIds = [],
    ): array {
        $referenceDate ??= Carbon::now();

        $ingredientIds = collect($recipeIngredientsState)
            ->pluck('ingredient_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $commercialProductIds = collect($recipeIngredientsState)
            ->pluck('commercial_product_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($ingredientIds === [] && $commercialProductIds === []) {
            return $this->calculateEntries([], $yieldPercentage, $portions, $referenceDate, $visitedRecipeIds);
        }

        $ingredients = Ingredient::query()
            ->whereIn('id', $ingredientIds)
            ->with(['baseUnit', 'recipe', 'ingredientSuppliers.supplier'])
            ->get()
            ->keyBy('id');

        $commercialProducts = CommercialProduct::query()
            ->whereIn('id', $commercialProductIds)
            ->get()
            ->keyBy('id');

        $entries = [];

        foreach ($recipeIngredientsState as $row) {
            $quantity = (float) ($row['quantity'] ?? 0);

            if ($quantity <= 0) {
                continue;
            }

            $ingredientId = (int) ($row['ingredient_id'] ?? 0);
            $ingredient = $ingredients->get($ingredientId);

            if ($ingredient instanceof Ingredient) {
                $entries[] = [
                    'ingredient' => $ingredient,
                    'quantity' => $quantity,
                    'unit_code' => (string) ($row['unit_code'] ?? $ingredient->base_unit_code),
                ];
            }

            $commercialProductId = (int) ($row['commercial_product_id'] ?? 0);
            $commercialProduct = $commercialProducts->get($commercialProductId);

            if ($commercialProduct instanceof CommercialProduct) {
                $entries[] = [
                    'commercial_product' => $commercialProduct,
                    'quantity' => $quantity,
                    'unit_code' => (string) ($row['unit_code'] ?? 'kg'),
                ];
            }
        }

        return $this->calculateEntries(
            $entries,
            $yieldPercentage,
            $portions,
            $referenceDate,
            $visitedRecipeIds,
        );
    }

    public function recalculateAndPersistTotalWeight(Recipe $recipe): void
    {
        try {
            $result = $this->calculate($recipe);
            $recipe->forceFill(['total_weight' => $result['total_weight']])->saveQuietly();
        } catch (RuntimeException) {
            $recipe->forceFill(['total_weight' => null])->saveQuietly();
        }
    }

    protected function calculateEntries(
        array $entries,
        float $yieldPercentage,
        int $portions,
        CarbonInterface $referenceDate,
        array $visitedRecipeIds,
    ): array {
        $totalRawCost = 0.0;
        $totalRawWeight = 0.0;
        $currency = null;

        foreach ($entries as $entry) {
            $ingredient = $entry['ingredient'] ?? null;
            $commercialProduct = $entry['commercial_product'] ?? null;
            $quantity = max(0.0, (float) ($entry['quantity'] ?? 0));

            if ($quantity <= 0) {
                continue;
            }

            if ($ingredient instanceof Ingredient) {
                $unitCode = (string) ($entry['unit_code'] ?? $ingredient->base_unit_code);
                $quantityInBaseUnit = $this->unitConversionService->convert(
                    $quantity,
                    $unitCode,
                    (string) $ingredient->base_unit_code,
                );
                $totalRawWeight += $this->resolveQuantityInGrams($quantity, $unitCode, $ingredient);

                if ($ingredient->recipe_id !== null) {
                    $compoundRecipe = $ingredient->recipe;

                    if ($compoundRecipe === null) {
                        continue;
                    }

                    $compoundCost = $this->calculate($compoundRecipe, $referenceDate, $visitedRecipeIds);
                    $compoundCostPerBaseUnit = $this->resolveSemiFinishedCostPerBaseUnit($ingredient, $compoundCost);
                    $lineCost = $compoundCostPerBaseUnit * $quantityInBaseUnit;

                    $this->assertCurrency($currency, $compoundCost['currency'] ?? null);
                    $totalRawCost += $lineCost;

                    continue;
                }

                $pricing = $this->ingredientPricingService->calculateCostForBaseQuantity(
                    $ingredient,
                    $quantityInBaseUnit,
                    $referenceDate,
                );

                $this->assertCurrency($currency, $pricing['currency'] ?? null);
                $totalRawCost += (float) ($pricing['cost'] ?? 0);

                continue;
            }

            if ($commercialProduct instanceof CommercialProduct) {
                $unitCode = (string) ($entry['unit_code'] ?? 'kg');
                $totalRawWeight += $this->resolveCommercialProductQuantityInGrams($quantity, $unitCode);
            }
        }

        $yieldFactor = $this->normalizeYieldFactor($yieldPercentage);
        $yieldAdjustedTotalCost = $totalRawCost / $yieldFactor;
        $totalWeight = $totalRawWeight * $yieldFactor;
        $costPer100g = $totalWeight > 0 ? ($yieldAdjustedTotalCost / $totalWeight) * 100 : 0.0;
        $costPerPortion = $portions > 0 ? $yieldAdjustedTotalCost / $portions : 0.0;

        return [
            'raw_total_cost' => $totalRawCost,
            'total_cost' => $yieldAdjustedTotalCost,
            'raw_total_weight' => $totalRawWeight,
            'total_weight' => $totalWeight,
            'cost_per_100g' => $costPer100g,
            'cost_per_portion' => $costPerPortion,
            'yield_percentage' => $yieldPercentage,
            'portions' => $portions,
            'currency' => $currency ?? 'EUR',
        ];
    }

    protected function resolveQuantityInGrams(float $quantity, string $unitCode, Ingredient $ingredient): float
    {
        $direct = $this->unitConversionService->tryConvert($quantity, $unitCode, 'g');

        if ($direct !== null) {
            return $direct;
        }

        $quantityInBaseUnit = $this->unitConversionService->tryConvert(
            $quantity,
            $unitCode,
            (string) $ingredient->base_unit_code,
        );

        if ($quantityInBaseUnit === null) {
            return 0.0;
        }

        return $this->unitConversionService->tryConvert(
            $quantityInBaseUnit,
            (string) $ingredient->base_unit_code,
            'g',
        ) ?? 0.0;
    }

    protected function resolveCommercialProductQuantityInGrams(float $quantity, string $unitCode): float
    {
        return $this->unitConversionService->tryConvert($quantity, $unitCode, 'g') ?? 0.0;
    }

    protected function resolveSemiFinishedCostPerBaseUnit(Ingredient $ingredient, array $compoundCost): float
    {
        $baseUnitDimension = $ingredient->baseUnit?->dimension;
        $isEach = $baseUnitDimension instanceof UnitDimension
            ? $baseUnitDimension === UnitDimension::Each
            : ((string) $baseUnitDimension === UnitDimension::Each->value);

        if ($isEach) {
            $unitsProduced = max(1, (int) ($compoundCost['portions'] ?? 1));

            return ((float) ($compoundCost['total_cost'] ?? 0)) / $unitsProduced;
        }

        $compoundWeightInBaseUnit = $this->unitConversionService->tryConvert(
            (float) ($compoundCost['total_weight'] ?? 0),
            'g',
            (string) $ingredient->base_unit_code,
        );

        if ($compoundWeightInBaseUnit === null || $compoundWeightInBaseUnit <= 0) {
            throw new RuntimeException("Impossibile risolvere la conversione del semilavorato nell'unità base [{$ingredient->base_unit_code}] per l'ingrediente [{$ingredient->id}].");
        }

        return ((float) ($compoundCost['total_cost'] ?? 0)) / $compoundWeightInBaseUnit;
    }

    protected function normalizeYieldFactor(float $yieldPercentage): float
    {
        return $yieldPercentage > 0 ? $yieldPercentage / 100 : 1.0;
    }

    protected function assertCurrency(?string &$currency, ?string $lineCurrency): void
    {
        if (blank($lineCurrency)) {
            return;
        }

        if ($currency === null) {
            $currency = $lineCurrency;

            return;
        }

        if ($currency !== $lineCurrency) {
            throw new RuntimeException("Valute miste non supportate nei calcoli ricetta: [{$currency}] e [{$lineCurrency}].");
        }
    }
}
