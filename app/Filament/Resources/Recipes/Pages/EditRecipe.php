<?php

namespace App\Filament\Resources\Recipes\Pages;

use App\Filament\Resources\Recipes\RecipeResource;
use App\Services\Recipes\RecipeIngredientNoteService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditRecipe extends EditRecord
{
    protected static string $resource = RecipeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $state = $this->form->getState();

        $rows = $state['recipeIngredients'] ?? $data['recipeIngredients'] ?? [];

        $data['description'] = app(RecipeIngredientNoteService::class)
            ->generateFromForm($rows);

        return $data;
    }

    protected function afterSave(): void
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
