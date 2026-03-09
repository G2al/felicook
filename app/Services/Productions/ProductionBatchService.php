<?php

declare(strict_types=1);

namespace App\Services\Productions;

use App\Enums\AllergenPresenceType;
use App\Enums\ProductionItemSourceType;
use App\Models\CommercialProduct;
use App\Models\Ingredient;
use App\Models\ProductionBatch;
use App\Models\ProductionBatchItem;
use App\Models\Recipe;
use App\Services\Recipes\RecipeAllergenService;
use App\Services\Recipes\RecipeFoodCostService;
use App\Services\Recipes\RecipeNutritionService;
use App\Services\Units\UnitConversionService;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class ProductionBatchService
{
    public function __construct(
        protected ProductionLotCodeService $productionLotCodeService,
        protected RecipeFoodCostService $recipeFoodCostService,
        protected RecipeNutritionService $recipeNutritionService,
        protected RecipeAllergenService $recipeAllergenService,
        protected UnitConversionService $unitConversionService,
    ) {}

    public function prepareItemsFromRecipe(int $recipeId, ?string $fallbackExpiresAt = null): array
    {
        $recipe = Recipe::query()
            ->with([
                'recipeIngredients' => fn ($query) => $query->orderBy('sort_order'),
                'recipeIngredients.ingredient.baseUnit',
                'recipeIngredients.commercialProduct',
            ])
            ->find($recipeId);

        if (! $recipe instanceof Recipe) {
            return [];
        }

        $items = [];

        foreach ($recipe->recipeIngredients as $index => $recipeIngredient) {
            $ingredient = $recipeIngredient->ingredient;
            $commercialProduct = $recipeIngredient->commercialProduct;
            $sourceType = $ingredient !== null ? ProductionItemSourceType::Ingredient : ProductionItemSourceType::CommercialProduct;
            $unitCode = (string) ($recipeIngredient->unit_code ?: ($ingredient?->base_unit_code ?: 'kg'));
            $quantity = (float) $recipeIngredient->quantity;

            if ($quantity <= 0) {
                continue;
            }

            if ($ingredient === null && $commercialProduct === null) {
                continue;
            }

            $tracking = $this->resolveLatestTracking(
                $sourceType,
                $ingredient?->id,
                $commercialProduct?->id,
            );

            $expiresAt = $tracking['expires_at'] ?? $fallbackExpiresAt;

            $items[] = [
                'source_type' => $sourceType->value,
                'ingredient_id' => $ingredient?->id,
                'commercial_product_id' => $commercialProduct?->id,
                'name_snapshot' => (string) ($ingredient?->name ?? $commercialProduct?->name),
                'quantity' => $quantity,
                'unit_code' => $unitCode,
                'quantity_in_grams' => $this->resolveQuantityInGrams($quantity, $unitCode, $ingredient),
                'lot_code' => $tracking['lot_code'] ?? null,
                'expires_at' => $expiresAt,
                'sort_order' => (int) ($recipeIngredient->sort_order ?? $index),
                'meta' => [
                    'from_recipe_ingredient_id' => $recipeIngredient->id,
                ],
            ];
        }

        return $items;
    }

    public function hydratePayload(array $data, ?ProductionBatch $record = null): array
    {
        $recipeId = (int) ($data['recipe_id'] ?? $record?->recipe_id ?? 0);
        $recipe = $recipeId > 0
            ? Recipe::query()
                ->with([
                    'recipeIngredients' => fn ($query) => $query->orderBy('sort_order'),
                    'recipeIngredients.ingredient.baseUnit',
                    'recipeIngredients.ingredient.recipe',
                    'recipeIngredients.ingredient.ingredientSuppliers.supplier',
                    'recipeIngredients.ingredient.nutritionalValue',
                    'recipeIngredients.ingredient.allergens',
                    'recipeIngredients.commercialProduct.nutritionalValue',
                    'recipeIngredients.commercialProduct.allergens',
                ])
                ->find($recipeId)
            : null;

        $productionDate = $this->resolveDate($data['production_date'] ?? $record?->production_date);
        $expiresAt = $this->resolveDate($data['expires_at'] ?? $record?->expires_at);
        $lotCode = trim((string) ($data['lot_code'] ?? $record?->lot_code));

        if ($productionDate === null) {
            $productionDate = Carbon::now();
        }

        if ($lotCode === '') {
            $lotCode = $this->productionLotCodeService->generate($productionDate);
        }

        $producedWeight = max(0.0, (float) ($data['produced_weight'] ?? $record?->produced_weight ?? 0));
        $currency = (string) ($data['currency'] ?? $record?->currency ?? 'EUR');
        $recipeTotalCost = 0.0;
        $recipeTotalWeight = 0.0;
        $totalCost = 0.0;
        $costPerKg = 0.0;
        $allergensSnapshot = $record?->allergens_snapshot ?? [];
        $nutritionSnapshot = $record?->nutrition_snapshot ?? [];
        $recipeSnapshot = $record?->recipe_snapshot ?? [];

        if ($recipe instanceof Recipe) {
            $foodCost = $this->recipeFoodCostService->calculate($recipe, $productionDate);
            $nutrition = $this->recipeNutritionService->calculate($recipe);
            $allergens = $this->recipeAllergenService->aggregate($recipe);
            $recipeTotalCost = (float) ($foodCost['total_cost'] ?? 0);
            $recipeTotalWeight = (float) ($foodCost['total_weight'] ?? 0);
            $currency = (string) ($foodCost['currency'] ?? $currency);
            $producedWeight = $producedWeight > 0 ? $producedWeight : $recipeTotalWeight;

            if ($recipeTotalWeight > 0) {
                $factor = $producedWeight / $recipeTotalWeight;
                $totalCost = $recipeTotalCost * $factor;
            }

            $costPerKg = $producedWeight > 0 ? $totalCost / ($producedWeight / 1000) : 0.0;
            $allergensSnapshot = $this->buildAllergenSnapshot($allergens);
            $nutritionSnapshot = $nutrition;
            $recipeSnapshot = [
                'id' => $recipe->id,
                'name' => (string) $recipe->name,
                'category' => (string) ($recipe->category ?? ''),
                'description' => (string) ($recipe->description ?? ''),
                'portions' => (int) ($recipe->portions ?? 0),
                'yield_percentage' => (float) ($recipe->yield_percentage ?? 0),
                'total_weight' => $recipeTotalWeight,
            ];
        }

        $rawItems = $data['items'] ?? [];

        if ((! is_array($rawItems) || $rawItems === []) && $recipe instanceof Recipe) {
            $rawItems = $this->prepareItemsFromRecipe(
                $recipe->id,
                $expiresAt?->toDateString(),
            );
        }

        $items = $this->normalizeItems(
            is_array($rawItems) ? $rawItems : [],
            $expiresAt?->toDateString(),
        );

        return array_merge($data, [
            'recipe_id' => $recipe?->id,
            'lot_code' => $lotCode,
            'production_date' => $productionDate->toDateString(),
            'expires_at' => $expiresAt?->toDateString(),
            'produced_weight' => $producedWeight,
            'currency' => $currency,
            'recipe_total_cost' => $recipeTotalCost,
            'recipe_total_weight' => $recipeTotalWeight,
            'total_cost' => $totalCost,
            'cost_per_kg' => $costPerKg,
            'allergens_snapshot' => $allergensSnapshot,
            'nutrition_snapshot' => $nutritionSnapshot,
            'recipe_snapshot' => $recipeSnapshot,
            'items' => $items,
        ]);
    }

    protected function normalizeItems(array $items, ?string $fallbackExpiresAt): array
    {
        $normalized = [];

        foreach (array_values($items) as $index => $item) {
            $ingredientId = isset($item['ingredient_id']) ? (int) $item['ingredient_id'] : null;
            $commercialProductId = isset($item['commercial_product_id']) ? (int) $item['commercial_product_id'] : null;
            $ingredient = ($ingredientId ?? 0) > 0 ? Ingredient::query()->withTrashed()->find($ingredientId) : null;
            $commercialProduct = ($commercialProductId ?? 0) > 0 ? CommercialProduct::query()->withTrashed()->find($commercialProductId) : null;
            $sourceType = ProductionItemSourceType::tryFrom((string) ($item['source_type'] ?? ''))
                ?? ($commercialProduct !== null ? ProductionItemSourceType::CommercialProduct : ProductionItemSourceType::Ingredient);
            $quantity = max(0.0, (float) ($item['quantity'] ?? 0));
            $unitCode = trim((string) ($item['unit_code'] ?? ($ingredient?->base_unit_code ?: 'kg')));
            $expiresAt = $this->toDateString($item['expires_at'] ?? null) ?? $fallbackExpiresAt;
            $lotCode = trim((string) ($item['lot_code'] ?? ''));
            $nameSnapshot = trim((string) ($item['name_snapshot'] ?? ''));

            if ($nameSnapshot === '') {
                $nameSnapshot = (string) ($ingredient?->name ?? $commercialProduct?->name ?? '');
            }

            $normalized[] = [
                'id' => isset($item['id']) ? (int) $item['id'] : null,
                'source_type' => $sourceType->value,
                'ingredient_id' => $ingredient?->id,
                'commercial_product_id' => $commercialProduct?->id,
                'name_snapshot' => $nameSnapshot,
                'quantity' => $quantity,
                'unit_code' => $unitCode,
                'quantity_in_grams' => $this->resolveQuantityInGrams($quantity, $unitCode, $ingredient),
                'lot_code' => $lotCode,
                'expires_at' => $expiresAt,
                'sort_order' => isset($item['sort_order']) ? (int) $item['sort_order'] : $index,
                'meta' => is_array($item['meta'] ?? null) ? $item['meta'] : [],
            ];
        }

        return $normalized;
    }

    protected function resolveQuantityInGrams(float $quantity, string $unitCode, ?Ingredient $ingredient): ?float
    {
        $direct = $this->unitConversionService->tryConvert($quantity, $unitCode, 'g');

        if ($direct !== null) {
            return $direct;
        }

        if (! $ingredient instanceof Ingredient) {
            return null;
        }

        $inBase = $this->unitConversionService->tryConvert($quantity, $unitCode, (string) $ingredient->base_unit_code);

        if ($inBase === null) {
            return null;
        }

        return $this->unitConversionService->tryConvert($inBase, (string) $ingredient->base_unit_code, 'g');
    }

    protected function resolveLatestTracking(
        ProductionItemSourceType $sourceType,
        ?int $ingredientId,
        ?int $commercialProductId,
    ): ?array {
        $query = ProductionBatchItem::query()
            ->select('production_batch_items.*')
            ->join('production_batches', 'production_batches.id', '=', 'production_batch_items.production_batch_id')
            ->whereNull('production_batches.deleted_at')
            ->orderByDesc('production_batches.production_date')
            ->orderByDesc('production_batch_items.id');

        if ($sourceType === ProductionItemSourceType::Ingredient && ($ingredientId ?? 0) > 0) {
            $query->where('production_batch_items.ingredient_id', $ingredientId);
        }

        if ($sourceType === ProductionItemSourceType::CommercialProduct && ($commercialProductId ?? 0) > 0) {
            $query->where('production_batch_items.commercial_product_id', $commercialProductId);
        }

        $item = $query->first();

        if (! $item instanceof ProductionBatchItem) {
            return null;
        }

        return [
            'lot_code' => (string) $item->lot_code,
            'expires_at' => $this->toDateString($item->expires_at),
        ];
    }

    protected function buildAllergenSnapshot(array $allergens): array
    {
        $contains = [];
        $mayContain = [];

        foreach ($allergens as $allergen) {
            $name = (string) ($allergen['name'] ?? '');
            $code = trim((string) ($allergen['code'] ?? ''));
            $presenceType = (string) ($allergen['presence_type'] ?? AllergenPresenceType::Contains->value);

            if ($name === '') {
                continue;
            }

            $payload = [
                'name' => $name,
                'code' => $code,
            ];

            if ($presenceType === AllergenPresenceType::Contains->value) {
                $contains[] = $payload;
                continue;
            }

            if ($presenceType === AllergenPresenceType::MayContain->value) {
                $mayContain[] = $payload;
            }
        }

        usort($contains, fn (array $left, array $right): int => strcmp((string) $left['name'], (string) $right['name']));
        usort($mayContain, fn (array $left, array $right): int => strcmp((string) $left['name'], (string) $right['name']));

        return [
            'contains' => $contains,
            'may_contain' => $mayContain,
        ];
    }

    protected function resolveDate(mixed $value): ?CarbonInterface
    {
        if ($value instanceof CarbonInterface) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return Carbon::parse($value);
    }

    protected function toDateString(mixed $value): ?string
    {
        if ($value instanceof CarbonInterface) {
            return $value->toDateString();
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return Carbon::parse($value)->toDateString();
    }
}
