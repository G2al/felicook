<?php

declare(strict_types=1);

namespace App\Services\Recipes;

use App\Models\CommercialProduct;
use App\Models\Ingredient;
use App\Services\Units\UnitConversionService;
use Illuminate\Support\Collection;
use Throwable;

class RecipeIngredientNoteService
{
    public function __construct(
        protected UnitConversionService $unitConversionService,
    ) {}

    public function generateFromForm(array $rows): string
    {
        $items = $this->prepareItems(collect($rows));

        if ($items === []) {
            return '';
        }

        $total = collect($items)->sum('weight');

        $parts = collect($items)
            ->sortByDesc('weight')
            ->map(function (array $item) use ($total): string {
                if ($total <= 0) {
                    return $item['name'];
                }

                $percent = ($item['weight'] / $total) * 100;

                return $item['name'] . ' (' . number_format($percent, 1, ',', '.') . '%)';
            })
            ->values()
            ->all();

        return 'Ingredienti: ' . implode(', ', $parts);
    }

    protected function prepareItems(Collection $rows): array
    {
        $ingredientIds = $rows->pluck('ingredient_id')->filter()->unique()->values();
        $commercialIds = $rows->pluck('commercial_product_id')->filter()->unique()->values();

        $ingredients = Ingredient::query()
            ->whereIn('id', $ingredientIds)
            ->get(['id', 'name', 'base_unit_code'])
            ->keyBy('id');

        $commercialProducts = CommercialProduct::query()
            ->whereIn('id', $commercialIds)
            ->get(['id', 'name'])
            ->keyBy('id');

        $items = [];

        foreach ($rows as $row) {
            $quantity = (float) ($row['quantity'] ?? 0);
            if ($quantity <= 0) {
                continue;
            }

            $ingredientId = (int) ($row['ingredient_id'] ?? 0);
            $commercialId = (int) ($row['commercial_product_id'] ?? 0);
            $unitCode = (string) ($row['unit_code'] ?? 'g');

            if ($ingredientId > 0 && $ingredients->has($ingredientId)) {
                $ingredient = $ingredients->get($ingredientId);
                $items[] = [
                    'name' => (string) $ingredient->name,
                    'weight' => $this->toGrams($quantity, $unitCode),
                ];
                continue;
            }

            if ($commercialId > 0 && $commercialProducts->has($commercialId)) {
                $product = $commercialProducts->get($commercialId);
                $items[] = [
                    'name' => (string) $product->name,
                    'weight' => $this->toGrams($quantity, $unitCode),
                ];
            }
        }

        return $items;
    }

    protected function toGrams(float $quantity, string $unitCode): float
    {
        $unit = strtolower($unitCode);

        if ($unit === 'g') {
            return $quantity;
        }

        if ($unit === 'kg') {
            return $quantity * 1000;
        }

        try {
            return (float) $this->unitConversionService->convert($quantity, $unitCode, 'g');
        } catch (Throwable) {
            return $quantity;
        }
    }
}
