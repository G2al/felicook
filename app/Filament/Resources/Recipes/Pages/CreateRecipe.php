<?php

namespace App\Filament\Resources\Recipes\Pages;

use App\Filament\Resources\Recipes\RecipeResource;
use App\Services\Recipes\RecipeIngredientNoteService;
use Filament\Resources\Pages\CreateRecord;

class CreateRecipe extends CreateRecord
{
    protected static string $resource = RecipeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $state = $this->form->getState();

        $rows = $state['recipeIngredients'] ?? $data['recipeIngredients'] ?? [];

        $data['description'] = app(RecipeIngredientNoteService::class)
            ->generateFromForm($rows);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->refreshDescriptionFromRecord();
    }

    protected function refreshDescriptionFromRecord(): void
    {
        $rows = $this->record->recipeIngredients()
            ->get(['ingredient_id', 'commercial_product_id', 'quantity', 'unit_code'])
            ->map(fn ($row) => [
                'ingredient_id' => $row->ingredient_id,
                'commercial_product_id' => $row->commercial_product_id,
                'quantity' => $row->quantity,
                'unit_code' => $row->unit_code,
            ])
            ->all();

        $description = app(RecipeIngredientNoteService::class)->generateFromForm($rows);

        $this->record->forceFill(['description' => $description])->saveQuietly();
    }
}
