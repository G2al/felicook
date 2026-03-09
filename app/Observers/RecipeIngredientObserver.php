<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Services\Recipes\RecipeFoodCostService;

class RecipeIngredientObserver
{
    public function __construct(
        protected RecipeFoodCostService $recipeFoodCostService,
    ) {}

    public function created(RecipeIngredient $recipeIngredient): void
    {
        $this->refreshRecipeTotalWeight((int) $recipeIngredient->recipe_id);
    }

    public function updated(RecipeIngredient $recipeIngredient): void
    {
        $originalRecipeId = (int) $recipeIngredient->getOriginal('recipe_id');

        if ($originalRecipeId !== (int) $recipeIngredient->recipe_id) {
            $this->refreshRecipeTotalWeight($originalRecipeId);
        }

        $this->refreshRecipeTotalWeight((int) $recipeIngredient->recipe_id);
    }

    public function deleted(RecipeIngredient $recipeIngredient): void
    {
        $this->refreshRecipeTotalWeight((int) $recipeIngredient->recipe_id);
    }

    public function restored(RecipeIngredient $recipeIngredient): void
    {
        $this->refreshRecipeTotalWeight((int) $recipeIngredient->recipe_id);
    }

    public function forceDeleted(RecipeIngredient $recipeIngredient): void
    {
        $this->refreshRecipeTotalWeight((int) $recipeIngredient->recipe_id);
    }

    protected function refreshRecipeTotalWeight(int $recipeId): void
    {
        if ($recipeId <= 0) {
            return;
        }

        $recipe = Recipe::query()->find($recipeId);

        if (! $recipe instanceof Recipe) {
            return;
        }

        $this->recipeFoodCostService->recalculateAndPersistTotalWeight($recipe);
    }
}
