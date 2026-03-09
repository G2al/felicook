<?php

declare(strict_types=1);

namespace App\Services\Recipes;

use App\Models\CommercialProduct;
use App\Models\Ingredient;

class RecipeIngredientSourceService
{
    public function search(string $search = '', int $limit = 50): array
    {
        $needle = trim($search);
        $options = [];

        $ingredients = Ingredient::query()
            ->where('is_active', true)
            ->when($needle !== '', function ($query) use ($needle): void {
                $query->where('name', 'like', '%' . $needle . '%');
            })
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'base_unit_code']);

        foreach ($ingredients as $ingredient) {
            $options[$this->ingredientKey((int) $ingredient->id)] = 'Ingrediente | ' . (string) $ingredient->name;
        }

        $commercialProducts = CommercialProduct::query()
            ->where('is_active', true)
            ->when($needle !== '', function ($query) use ($needle): void {
                $query->where(function ($subQuery) use ($needle): void {
                    $subQuery
                        ->where('name', 'like', '%' . $needle . '%')
                        ->orWhere('brand', 'like', '%' . $needle . '%');
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'brand']);

        foreach ($commercialProducts as $product) {
            $brand = trim((string) ($product->brand ?? ''));
            $label = 'Prodotto commerciale | ' . (string) $product->name;

            if ($brand !== '') {
                $label .= ' - ' . $brand;
            }

            $options[$this->commercialProductKey((int) $product->id)] = $label;
        }

        asort($options);

        return $options;
    }

    public function label(?string $sourceKey): ?string
    {
        if ($sourceKey === null || trim($sourceKey) === '') {
            return null;
        }

        $resolved = $this->resolve($sourceKey);

        if (($resolved['ingredient_id'] ?? null) !== null) {
            $ingredient = Ingredient::query()->find((int) $resolved['ingredient_id']);

            if ($ingredient === null) {
                return null;
            }

            return 'Ingrediente | ' . (string) $ingredient->name;
        }

        if (($resolved['commercial_product_id'] ?? null) !== null) {
            $product = CommercialProduct::query()->find((int) $resolved['commercial_product_id']);

            if ($product === null) {
                return null;
            }

            $brand = trim((string) ($product->brand ?? ''));
            $label = 'Prodotto commerciale | ' . (string) $product->name;

            if ($brand !== '') {
                $label .= ' - ' . $brand;
            }

            return $label;
        }

        return null;
    }

    public function resolve(?string $sourceKey): array
    {
        $sourceKey = trim((string) $sourceKey);

        if ($sourceKey === '' || ! str_contains($sourceKey, ':')) {
            return [
                'ingredient_id' => null,
                'commercial_product_id' => null,
                'default_unit_code' => 'g',
            ];
        }

        [$type, $id] = explode(':', $sourceKey, 2);
        $idValue = (int) $id;

        if ($type === 'ingredient' && $idValue > 0) {
            return [
                'ingredient_id' => $idValue,
                'commercial_product_id' => null,
                'default_unit_code' => 'g',
            ];
        }

        if ($type === 'commercial_product' && $idValue > 0) {
            return [
                'ingredient_id' => null,
                'commercial_product_id' => $idValue,
                'default_unit_code' => 'g',
            ];
        }

        return [
            'ingredient_id' => null,
            'commercial_product_id' => null,
            'default_unit_code' => 'g',
        ];
    }

    public function keyFromIds(?int $ingredientId, ?int $commercialProductId): ?string
    {
        if (($ingredientId ?? 0) > 0) {
            return $this->ingredientKey((int) $ingredientId);
        }

        if (($commercialProductId ?? 0) > 0) {
            return $this->commercialProductKey((int) $commercialProductId);
        }

        return null;
    }

    protected function ingredientKey(int $id): string
    {
        return 'ingredient:' . $id;
    }

    protected function commercialProductKey(int $id): string
    {
        return 'commercial_product:' . $id;
    }
}
