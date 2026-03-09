<?php

declare(strict_types=1);

namespace App\Services\Productions;

use App\Models\Allergen;
use App\Models\ProductionBatch;

class ProductionLabelDataService
{
    public function build(ProductionBatch $productionBatch): array
    {
        $productionBatch->loadMissing([
            'recipe',
            'items.ingredient',
            'items.commercialProduct',
        ]);

        $recipeSnapshot = is_array($productionBatch->recipe_snapshot) ? $productionBatch->recipe_snapshot : [];
        $allergensSnapshot = is_array($productionBatch->allergens_snapshot) ? $productionBatch->allergens_snapshot : [];
        $nutritionSnapshot = is_array($productionBatch->nutrition_snapshot) ? $productionBatch->nutrition_snapshot : [];
        $ingredients = [];

        foreach ($productionBatch->items as $item) {
            $name = (string) ($item->name_snapshot ?: $item->ingredient?->name ?: $item->commercialProduct?->name);

            if ($name === '') {
                continue;
            }

            $ingredients[] = [
                'nome' => $name,
                'quantita' => (float) $item->quantity,
                'unita' => (string) $item->unit_code,
                'lotto' => (string) $item->lot_code,
                'scadenza' => $item->expires_at?->format('d/m/Y'),
            ];
        }

        $containsItems = $this->normalizeAllergens($allergensSnapshot['contains'] ?? []);
        $mayContainItems = $this->normalizeAllergens($allergensSnapshot['may_contain'] ?? []);

        return [
            'nome_prodotto' => (string) ($recipeSnapshot['name'] ?? $productionBatch->recipe?->name ?? 'N/D'),
            'categoria' => (string) ($recipeSnapshot['category'] ?? $productionBatch->recipe?->category ?? ''),
            'descrizione' => (string) ($recipeSnapshot['description'] ?? $productionBatch->recipe?->description ?? ''),
            'ingredienti' => $ingredients,
            'ingredienti_testo' => $this->ingredientsText($ingredients),
            'allergeni_contiene' => array_map(fn (array $item): string => (string) $item['name'], $containsItems),
            'allergeni_puo_contenere' => array_map(fn (array $item): string => (string) $item['name'], $mayContainItems),
            'allergeni_contiene_items' => $containsItems,
            'allergeni_puo_contenere_items' => $mayContainItems,
            'tabella_nutrizionale' => $this->nutritionRows($nutritionSnapshot['per_100g'] ?? []),
            'tabella_nutrizionale_porzione' => $this->nutritionRows($nutritionSnapshot['per_portion'] ?? []),
            'lotto' => (string) $productionBatch->lot_code,
            'prodotto_il' => $productionBatch->production_date?->format('d/m/Y'),
            'da_consumare_entro' => $productionBatch->expires_at?->format('d/m/Y'),
            'peso_prodotto' => (float) $productionBatch->produced_weight,
            'costo_totale' => (float) $productionBatch->total_cost,
            'costo_kg' => (float) $productionBatch->cost_per_kg,
            'prezzo_pubblico_kg' => (float) ($productionBatch->public_price_per_kg ?? 0),
            'valuta' => (string) $productionBatch->currency,
            'porzioni' => (int) ($recipeSnapshot['portions'] ?? 0),
            'resa' => (float) ($recipeSnapshot['yield_percentage'] ?? 0),
            'logo_path' => $this->logoPath(),
        ];
    }

    protected function normalizeAllergens(array $allergens): array
    {
        $normalized = [];

        foreach ($allergens as $allergen) {
            if (is_string($allergen)) {
                $name = trim($allergen);
                $code = '';
            } else {
                $name = trim((string) ($allergen['name'] ?? ''));
                $code = strtoupper(trim((string) ($allergen['code'] ?? '')));
            }

            if ($name === '') {
                continue;
            }

            if ($code === '') {
                $resolvedCode = Allergen::query()
                    ->where('name', $name)
                    ->value('code');

                if ($resolvedCode !== null) {
                    $code = strtoupper((string) $resolvedCode);
                }
            }

            $normalized[] = [
                'name' => $name,
                'code' => $code,
                'icon_path' => $this->allergenIconPath($code),
            ];
        }

        usort($normalized, fn (array $left, array $right): int => strcmp((string) $left['name'], (string) $right['name']));

        return $normalized;
    }

    protected function logoPath(): ?string
    {
        $path = public_path('images/logo.png');

        return is_file($path) ? $path : null;
    }

    protected function allergenIconPath(string $code): ?string
    {
        if ($code === '') {
            return null;
        }

        $path = public_path('images/allergeni/' . $code . '.svg');

        return is_file($path) ? $path : null;
    }

    protected function ingredientsText(array $ingredients): string
    {
        $names = array_values(array_filter(array_map(
            fn (array $ingredient): string => (string) ($ingredient['nome'] ?? ''),
            $ingredients,
        )));

        if ($names === []) {
            return 'N/D';
        }

        return implode(', ', $names);
    }

    protected function nutritionRows(array $values): array
    {
        return [
            ['label' => 'Energia', 'value' => (float) ($values['energy_kj'] ?? 0), 'unit' => 'kJ'],
            ['label' => 'Energia', 'value' => (float) ($values['energy_kcal'] ?? 0), 'unit' => 'kcal'],
            ['label' => 'Grassi', 'value' => (float) ($values['fat'] ?? 0), 'unit' => 'g'],
            ['label' => 'di cui acidi grassi saturi', 'value' => (float) ($values['saturated_fat'] ?? 0), 'unit' => 'g'],
            ['label' => 'Carboidrati', 'value' => (float) ($values['carbs'] ?? 0), 'unit' => 'g'],
            ['label' => 'di cui zuccheri', 'value' => (float) ($values['sugars'] ?? 0), 'unit' => 'g'],
            ['label' => 'Proteine', 'value' => (float) ($values['protein'] ?? 0), 'unit' => 'g'],
            ['label' => 'Sale', 'value' => (float) ($values['salt'] ?? 0), 'unit' => 'g'],
        ];
    }
}
