<?php

declare(strict_types=1);

namespace App\Services\Ingredients;

use App\Enums\AllergenPresenceType;
use App\Models\Allergen;
use App\Models\Ingredient;
use App\Models\IngredientAllergen;
use App\Models\NutritionalValue;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class IngredientJsonImportService
{
    protected const BASE_UNIT_CODE = 'kg';

    protected const NUTRITION_MAP = [
        'kJ' => 'energy_kj',
        'Kcal' => 'energy_kcal',
        'lipidi' => 'fat',
        'grassi_saturi' => 'saturated_fat',
        'grassi_insaturi' => 'mono_fat',
        'grassi_poliinsaturi' => 'poly_fat',
        'carboidrati' => 'carbs',
        'zuccheri' => 'sugars',
        'polioli' => 'polyols',
        'eritritolo' => 'erythritol',
        'fibre' => 'fiber',
        'proteine' => 'protein',
        'sale' => 'salt',
        'alcol' => 'alcohol',
        'acqua' => 'water',
        'part_edibile' => 'edible_part_percentage',
    ];

    protected const ALLERGEN_CODE_MAP = [
        'glutine' => 'GLUTINE',
        'crostacei' => 'CROSTACEI',
        'uova' => 'UOVA',
        'pesce' => 'PESCE',
        'arachidi' => 'ARACHIDI',
        'soia' => 'SOIA',
        'latte' => 'LATTE',
        'frutta a guscio' => 'FRUTTA_GUSCIO',
        'sedano' => 'SEDANO',
        'sesamo' => 'SESAMO',
        'lupini' => 'LUPINI',
        'molluschi' => 'MOLLUSCHI',
    ];

    public function importFromFile(string $absolutePath, bool $dryRun = false): array
    {
        $normalized = $this->normalizeFromFile($absolutePath);

        $stats = [
            ...$normalized['stats'],
            'imported' => 0,
            'failed' => $normalized['stats']['failed'],
            'ingredients_created' => 0,
            'ingredients_updated' => 0,
            'ingredients_restored' => 0,
            'nutritional_created' => 0,
            'nutritional_updated' => 0,
            'nutritional_restored' => 0,
            'allergen_links_created' => 0,
            'allergen_links_restored' => 0,
            'allergen_links_deleted' => 0,
        ];

        $errors = $normalized['errors'];

        if ($dryRun || $normalized['rows'] === []) {
            return [
                'stats' => $stats,
                'errors' => $errors,
            ];
        }

        $this->assertBaseUnitExists();
        $allergenIdMap = $this->resolveAllergenIdMap();

        foreach ($normalized['rows'] as $row) {
            try {
                $result = DB::transaction(fn (): array => $this->importRow($row, $allergenIdMap));

                $stats['imported']++;
                $stats['ingredients_created'] += $result['ingredients_created'];
                $stats['ingredients_updated'] += $result['ingredients_updated'];
                $stats['ingredients_restored'] += $result['ingredients_restored'];
                $stats['nutritional_created'] += $result['nutritional_created'];
                $stats['nutritional_updated'] += $result['nutritional_updated'];
                $stats['nutritional_restored'] += $result['nutritional_restored'];
                $stats['allergen_links_created'] += $result['allergen_links_created'];
                $stats['allergen_links_restored'] += $result['allergen_links_restored'];
                $stats['allergen_links_deleted'] += $result['allergen_links_deleted'];
            } catch (Throwable $exception) {
                $stats['failed']++;
                $errors[] = [
                    'row' => $row['source_row'] ?? null,
                    'message' => $exception->getMessage(),
                ];
            }
        }

        return [
            'stats' => $stats,
            'errors' => $errors,
        ];
    }

    public function normalizeFromFile(string $absolutePath): array
    {
        if (! is_file($absolutePath)) {
            throw new RuntimeException("File JSON non trovato: [{$absolutePath}].");
        }

        $content = file_get_contents($absolutePath);

        if ($content === false) {
            throw new RuntimeException("Impossibile leggere il file JSON: [{$absolutePath}].");
        }

        try {
            $decoded = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable $exception) {
            throw new RuntimeException('JSON non valido.', previous: $exception);
        }

        if (! is_array($decoded)) {
            throw new RuntimeException('Il JSON deve contenere una lista di ingredienti.');
        }

        $rows = [];
        $errors = [];

        foreach ($decoded as $index => $entry) {
            try {
                $rows[] = $this->normalizeEntry($entry, $index);
            } catch (RuntimeException $exception) {
                $errors[] = [
                    'row' => $index,
                    'message' => $exception->getMessage(),
                ];
            }
        }

        return [
            'rows' => $rows,
            'errors' => $errors,
            'stats' => [
                'total' => count($decoded),
                'normalized' => count($rows),
                'failed' => count($errors),
            ],
        ];
    }

    protected function normalizeEntry(mixed $entry, int $index): array
    {
        if (! is_array($entry)) {
            throw new RuntimeException("Formato record non valido in riga {$index}.");
        }

        $name = trim((string) ($entry['nome_ingr'] ?? ''));

        if ($name === '') {
            throw new RuntimeException("Campo nome_ingr obbligatorio in riga {$index}.");
        }

        $allergens = $entry['allergeni'] ?? [];

        if (! is_array($allergens)) {
            throw new RuntimeException("Campo allergeni non valido in riga {$index}.");
        }

        $nutritionSource = $entry['valori_nutrizionali'] ?? null;

        if (! is_array($nutritionSource)) {
            throw new RuntimeException("Campo valori_nutrizionali non valido in riga {$index}.");
        }

        return [
            'source_row' => $index,
            'name' => $name,
            'base_unit_code' => self::BASE_UNIT_CODE,
            'allergen_codes' => $this->normalizeAllergens($allergens),
            'nutritional_values' => $this->normalizeNutrition($nutritionSource),
        ];
    }

    protected function normalizeAllergens(array $allergens): array
    {
        $codes = [];

        foreach ($allergens as $allergenName) {
            $normalizedName = mb_strtolower(trim((string) $allergenName));

            if ($normalizedName === '') {
                continue;
            }

            $code = self::ALLERGEN_CODE_MAP[$normalizedName] ?? null;

            if ($code === null) {
                throw new RuntimeException("Allergene non mappato: [{$allergenName}].");
            }

            $codes[$code] = $code;
        }

        return array_values($codes);
    }

    protected function normalizeNutrition(array $source): array
    {
        $values = [];

        foreach (self::NUTRITION_MAP as $jsonKey => $targetKey) {
            if (! array_key_exists($jsonKey, $source)) {
                throw new RuntimeException("Campo nutrizionale mancante: [{$jsonKey}].");
            }

            $values[$targetKey] = $this->normalizeDecimal($source[$jsonKey]);
        }

        return $values;
    }

    protected function normalizeDecimal(mixed $value): string
    {
        $raw = trim((string) $value);
        $normalized = str_replace(',', '.', $raw);

        if ($normalized === '' || ! is_numeric($normalized)) {
            throw new RuntimeException("Valore numerico non valido: [{$raw}].");
        }

        return (string) $normalized;
    }

    protected function importRow(array $row, array $allergenIdMap): array
    {
        $ingredient = Ingredient::withTrashed()
            ->where('name', (string) $row['name'])
            ->first();

        $ingredientCreated = false;
        $ingredientRestored = false;

        if (! $ingredient instanceof Ingredient) {
            $ingredient = new Ingredient();
            $ingredientCreated = true;
            $ingredient->name = (string) $row['name'];
        }

        $wasTrashedIngredient = $ingredient->exists && $ingredient->trashed();

        $ingredient->forceFill([
            'base_unit_code' => (string) $row['base_unit_code'],
            'is_active' => true,
        ])->save();

        if ($wasTrashedIngredient) {
            $ingredient->restore();
            $ingredientRestored = true;
        }

        $nutrition = NutritionalValue::withTrashed()
            ->where('ingredient_id', (int) $ingredient->id)
            ->first();

        $nutritionCreated = false;
        $nutritionRestored = false;

        if (! $nutrition instanceof NutritionalValue) {
            $nutrition = new NutritionalValue();
            $nutritionCreated = true;
            $nutrition->ingredient_id = (int) $ingredient->id;
        }

        $wasTrashedNutrition = $nutrition->exists && $nutrition->trashed();

        $nutrition->forceFill($row['nutritional_values'])->save();

        if ($wasTrashedNutrition) {
            $nutrition->restore();
            $nutritionRestored = true;
        }

        $allergenSync = $this->syncContainsAllergens(
            (int) $ingredient->id,
            (array) $row['allergen_codes'],
            $allergenIdMap,
        );

        return [
            'ingredients_created' => $ingredientCreated ? 1 : 0,
            'ingredients_updated' => $ingredientCreated ? 0 : 1,
            'ingredients_restored' => $ingredientRestored ? 1 : 0,
            'nutritional_created' => $nutritionCreated ? 1 : 0,
            'nutritional_updated' => $nutritionCreated ? 0 : 1,
            'nutritional_restored' => $nutritionRestored ? 1 : 0,
            'allergen_links_created' => $allergenSync['created'],
            'allergen_links_restored' => $allergenSync['restored'],
            'allergen_links_deleted' => $allergenSync['deleted'],
        ];
    }

    protected function syncContainsAllergens(int $ingredientId, array $codes, array $allergenIdMap): array
    {
        $desiredAllergenIds = [];

        foreach ($codes as $code) {
            $allergenId = $allergenIdMap[$code] ?? null;

            if ($allergenId === null) {
                throw new RuntimeException("Allergene non presente a database: [{$code}].");
            }

            $desiredAllergenIds[$allergenId] = $allergenId;
        }

        $created = 0;
        $restored = 0;

        foreach ($desiredAllergenIds as $allergenId) {
            $link = IngredientAllergen::withTrashed()
                ->where('ingredient_id', $ingredientId)
                ->where('allergen_id', $allergenId)
                ->where('presence_type', AllergenPresenceType::Contains->value)
                ->first();

            if (! $link instanceof IngredientAllergen) {
                IngredientAllergen::query()->create([
                    'ingredient_id' => $ingredientId,
                    'allergen_id' => $allergenId,
                    'presence_type' => AllergenPresenceType::Contains->value,
                ]);

                $created++;

                continue;
            }

            if ($link->trashed()) {
                $link->restore();
                $restored++;
            }
        }

        $toDeleteQuery = IngredientAllergen::query()
            ->where('ingredient_id', $ingredientId)
            ->where('presence_type', AllergenPresenceType::Contains->value)
            ->whereNull('deleted_at');

        if ($desiredAllergenIds !== []) {
            $toDeleteQuery->whereNotIn('allergen_id', array_values($desiredAllergenIds));
        }

        $deleted = $toDeleteQuery->update([
            'deleted_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'created' => $created,
            'restored' => $restored,
            'deleted' => (int) $deleted,
        ];
    }

    protected function assertBaseUnitExists(): void
    {
        $exists = Unit::query()->whereKey(self::BASE_UNIT_CODE)->exists();

        if (! $exists) {
            throw new RuntimeException("Unita base non trovata: [" . self::BASE_UNIT_CODE . '].');
        }
    }

    protected function resolveAllergenIdMap(): array
    {
        $requiredCodes = array_values(array_unique(self::ALLERGEN_CODE_MAP));

        $resolved = Allergen::query()
            ->whereIn('code', $requiredCodes)
            ->pluck('id', 'code')
            ->all();

        $missing = array_values(array_diff($requiredCodes, array_keys($resolved)));

        if ($missing !== []) {
            throw new RuntimeException('Allergeni mancanti a database: [' . implode(', ', $missing) . '].');
        }

        $map = [];

        foreach ($resolved as $code => $id) {
            $map[(string) $code] = (int) $id;
        }

        return $map;
    }
}
