<?php

declare(strict_types=1);

namespace App\Services\Recipes;

use App\Enums\AllergenPresenceType;
use App\Models\Allergen;
use App\Models\CommercialProduct;
use App\Models\Ingredient;
use App\Models\Recipe;
use RuntimeException;

class RecipeAllergenService
{
    public function aggregate(Recipe $recipe, array $visitedRecipeIds = []): array
    {
        if ($recipe->id !== null) {
            if (in_array($recipe->id, $visitedRecipeIds, true)) {
                throw new RuntimeException("Riferimento ricorsivo rilevato per la ricetta [{$recipe->id}].");
            }

            $visitedRecipeIds[] = $recipe->id;
        }

        $recipe->loadMissing([
            'recipeIngredients.ingredient.recipe',
            'recipeIngredients.ingredient.allergens',
            'recipeIngredients.commercialProduct.allergens',
        ]);

        $aggregated = [];

        foreach ($recipe->recipeIngredients as $recipeIngredient) {
            $ingredient = $recipeIngredient->ingredient;
            $commercialProduct = $recipeIngredient->commercialProduct;

            if ($ingredient instanceof Ingredient) {
                $ingredientAllergens = $this->ingredientAllergens($ingredient, $visitedRecipeIds);
                $aggregated = $this->mergeAllergens($aggregated, $ingredientAllergens);
            }

            if ($commercialProduct instanceof CommercialProduct) {
                $commercialAllergens = $this->commercialProductAllergens($commercialProduct);
                $aggregated = $this->mergeAllergens($aggregated, $commercialAllergens);
            }
        }

        uasort($aggregated, fn (array $left, array $right): int => strcmp($left['name'], $right['name']));

        return array_values($aggregated);
    }

    protected function ingredientAllergens(Ingredient $ingredient, array $visitedRecipeIds): array
    {
        $aggregated = [];

        foreach ($ingredient->allergens as $allergen) {
            if (! $allergen instanceof Allergen) {
                continue;
            }

            $presenceType = (string) ($allergen->pivot->presence_type ?? AllergenPresenceType::Contains->value);
            $aggregated[$allergen->id] = [
                'id' => $allergen->id,
                'name' => (string) $allergen->name,
                'code' => (string) $allergen->code,
                'presence_type' => $presenceType,
            ];
        }

        if ($ingredient->recipe_id !== null && $ingredient->recipe !== null) {
            $compoundAllergens = $this->aggregate($ingredient->recipe, $visitedRecipeIds);

            $mapped = [];

            foreach ($compoundAllergens as $allergen) {
                $mapped[$allergen['id']] = $allergen;
            }

            $aggregated = $this->mergeAllergens($aggregated, $mapped);
        }

        return $aggregated;
    }

    protected function commercialProductAllergens(CommercialProduct $commercialProduct): array
    {
        $aggregated = [];

        foreach ($commercialProduct->allergens as $allergen) {
            if (! $allergen instanceof Allergen) {
                continue;
            }

            $presenceType = (string) ($allergen->pivot->presence_type ?? AllergenPresenceType::Contains->value);
            $aggregated[$allergen->id] = [
                'id' => $allergen->id,
                'name' => (string) $allergen->name,
                'code' => (string) $allergen->code,
                'presence_type' => $presenceType,
            ];
        }

        return $aggregated;
    }

    protected function mergeAllergens(array $base, array $incoming): array
    {
        foreach ($incoming as $allergenId => $payload) {
            if (! isset($base[$allergenId])) {
                $base[$allergenId] = $payload;

                continue;
            }

            if (
                $this->presencePriority((string) $payload['presence_type']) >
                $this->presencePriority((string) $base[$allergenId]['presence_type'])
            ) {
                $base[$allergenId]['presence_type'] = $payload['presence_type'];
            }
        }

        return $base;
    }

    protected function presencePriority(string $presenceType): int
    {
        return match ($presenceType) {
            AllergenPresenceType::Contains->value => 2,
            AllergenPresenceType::MayContain->value => 1,
            default => 0,
        };
    }
}
