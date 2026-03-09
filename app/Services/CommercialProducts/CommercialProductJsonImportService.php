<?php

declare(strict_types=1);

namespace App\Services\CommercialProducts;

use App\Enums\AllergenPresenceType;
use App\Models\Allergen;
use App\Models\CommercialProduct;
use App\Models\CommercialProductAllergen;
use App\Models\CommercialProductNutritionalValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class CommercialProductJsonImportService
{
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
        'senape' => 'SENAPE',
        'sesamo' => 'SESAMO',
        'semi di sesamo' => 'SESAMO',
        'solfiti' => 'SOLFITI',
        'anidride solforosa e solfiti' => 'SOLFITI',
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
            'products_created' => 0,
            'products_updated' => 0,
            'products_restored' => 0,
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

        $allergenIdMap = $this->resolveAllergenIdMap();

        foreach ($normalized['rows'] as $row) {
            try {
                $result = DB::transaction(fn (): array => $this->importRow($row, $allergenIdMap));

                $stats['imported']++;
                $stats['products_created'] += $result['products_created'];
                $stats['products_updated'] += $result['products_updated'];
                $stats['products_restored'] += $result['products_restored'];
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
            throw new RuntimeException('Il JSON deve contenere una lista di prodotti commerciali.');
        }

        $rowsByKey = [];
        $errors = [];
        $duplicatesMerged = 0;

        foreach ($decoded as $index => $entry) {
            try {
                $row = $this->normalizeEntry($entry, $index);
                $key = $this->rowKey((string) $row['name'], $row['brand']);

                if (isset($rowsByKey[$key])) {
                    $rowsByKey[$key] = $this->mergeRows($rowsByKey[$key], $row);
                    $duplicatesMerged++;

                    continue;
                }

                $rowsByKey[$key] = $row;
            } catch (RuntimeException $exception) {
                $errors[] = [
                    'row' => $index,
                    'message' => $exception->getMessage(),
                ];
            }
        }

        return [
            'rows' => array_values($rowsByKey),
            'errors' => $errors,
            'stats' => [
                'total' => count($decoded),
                'normalized' => count($rowsByKey),
                'failed' => count($errors),
                'duplicates_merged' => $duplicatesMerged,
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

        $brandRaw = trim((string) ($entry['brand_name'] ?? ''));
        $brand = $brandRaw !== '' ? $brandRaw : null;
        $categoryRaw = trim((string) ($entry['category'] ?? ''));
        $category = $categoryRaw !== '' ? $categoryRaw : null;
        $ingredientListRaw = trim((string) ($entry['lista_ingredienti'] ?? ''));
        $ingredientList = $ingredientListRaw !== '' ? $ingredientListRaw : null;
        $allergens = $entry['allergeni'] ?? [];

        if (! is_array($allergens)) {
            throw new RuntimeException("Campo allergeni non valido in riga {$index}.");
        }

        $nutritionSource = $entry['valori_nutrizionali'] ?? null;
        $nutritionArray = is_array($nutritionSource) ? $nutritionSource : [];

        return [
            'source_row' => $index,
            'name' => $name,
            'brand' => $brand,
            'category' => $category,
            'ingredient_list' => $ingredientList,
            'allergen_codes' => $this->normalizeAllergens($allergens),
            'nutritional_values' => $this->normalizeNutrition($nutritionArray),
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

    protected function normalizeNutrition(array $source): ?array
    {
        $values = [];
        $hasAtLeastOneValue = false;

        foreach (self::NUTRITION_MAP as $jsonKey => $targetKey) {
            if (! array_key_exists($jsonKey, $source)) {
                $values[$targetKey] = null;

                continue;
            }

            $normalized = $this->normalizeDecimalOrNull($source[$jsonKey]);
            $values[$targetKey] = $normalized;

            if ($normalized !== null) {
                $hasAtLeastOneValue = true;
            }
        }

        return $hasAtLeastOneValue ? $values : null;
    }

    protected function normalizeDecimalOrNull(mixed $value): ?string
    {
        $raw = trim((string) $value);

        if ($raw === '') {
            return null;
        }

        $normalized = str_replace(',', '.', $raw);

        if (! is_numeric($normalized)) {
            throw new RuntimeException("Valore numerico non valido: [{$raw}].");
        }

        return $normalized;
    }

    protected function mergeRows(array $current, array $incoming): array
    {
        $currentAllergens = $current['allergen_codes'] ?? [];
        $incomingAllergens = $incoming['allergen_codes'] ?? [];
        $allergenCodes = array_values(array_unique([...$currentAllergens, ...$incomingAllergens]));

        $category = $current['category'] ?? null;

        if ($category === null && ($incoming['category'] ?? null) !== null) {
            $category = $incoming['category'];
        }

        $ingredientList = $this->preferredText(
            $current['ingredient_list'] ?? null,
            $incoming['ingredient_list'] ?? null,
        );

        $nutritionalValues = $this->preferredNutrition(
            $current['nutritional_values'] ?? null,
            $incoming['nutritional_values'] ?? null,
        );

        return [
            ...$current,
            'category' => $category,
            'ingredient_list' => $ingredientList,
            'allergen_codes' => $allergenCodes,
            'nutritional_values' => $nutritionalValues,
        ];
    }

    protected function preferredText(?string $current, ?string $incoming): ?string
    {
        if ($current === null || $current === '') {
            return $incoming;
        }

        if ($incoming === null || $incoming === '') {
            return $current;
        }

        return mb_strlen($incoming) > mb_strlen($current) ? $incoming : $current;
    }

    protected function preferredNutrition(?array $current, ?array $incoming): ?array
    {
        if (! is_array($current)) {
            return $incoming;
        }

        if (! is_array($incoming)) {
            return $current;
        }

        return $this->countFilledNutritionValues($incoming) > $this->countFilledNutritionValues($current)
            ? $incoming
            : $current;
    }

    protected function countFilledNutritionValues(array $values): int
    {
        $count = 0;

        foreach ($values as $value) {
            if ($value !== null && $value !== '') {
                $count++;
            }
        }

        return $count;
    }

    protected function importRow(array $row, array $allergenIdMap): array
    {
        $product = $this->findProductByNameAndBrand(
            (string) $row['name'],
            $row['brand'],
        );

        $productCreated = false;
        $productRestored = false;

        if (! $product instanceof CommercialProduct) {
            $product = new CommercialProduct();
            $productCreated = true;
            $product->name = (string) $row['name'];
            $product->brand = $row['brand'];
        }

        $wasTrashedProduct = $product->exists && $product->trashed();

        $product->forceFill([
            'category' => $row['category'],
            'ingredient_list' => $row['ingredient_list'],
            'is_active' => true,
        ])->save();

        if ($wasTrashedProduct) {
            $product->restore();
            $productRestored = true;
        }

        $nutritionCreated = 0;
        $nutritionUpdated = 0;
        $nutritionRestored = 0;

        if (is_array($row['nutritional_values'])) {
            $nutrition = CommercialProductNutritionalValue::withTrashed()
                ->where('commercial_product_id', (int) $product->id)
                ->first();

            $nutritionCreatedFlag = false;
            $nutritionRestoredFlag = false;

            if (! $nutrition instanceof CommercialProductNutritionalValue) {
                $nutrition = new CommercialProductNutritionalValue();
                $nutritionCreatedFlag = true;
                $nutrition->commercial_product_id = (int) $product->id;
            }

            $wasTrashedNutrition = $nutrition->exists && $nutrition->trashed();
            $nutrition->forceFill($row['nutritional_values'])->save();

            if ($wasTrashedNutrition) {
                $nutrition->restore();
                $nutritionRestoredFlag = true;
            }

            $nutritionCreated = $nutritionCreatedFlag ? 1 : 0;
            $nutritionUpdated = $nutritionCreatedFlag ? 0 : 1;
            $nutritionRestored = $nutritionRestoredFlag ? 1 : 0;
        }

        $allergenSync = $this->syncContainsAllergens(
            (int) $product->id,
            (array) $row['allergen_codes'],
            $allergenIdMap,
        );

        return [
            'products_created' => $productCreated ? 1 : 0,
            'products_updated' => $productCreated ? 0 : 1,
            'products_restored' => $productRestored ? 1 : 0,
            'nutritional_created' => $nutritionCreated,
            'nutritional_updated' => $nutritionUpdated,
            'nutritional_restored' => $nutritionRestored,
            'allergen_links_created' => $allergenSync['created'],
            'allergen_links_restored' => $allergenSync['restored'],
            'allergen_links_deleted' => $allergenSync['deleted'],
        ];
    }

    protected function findProductByNameAndBrand(string $name, ?string $brand): ?CommercialProduct
    {
        return CommercialProduct::withTrashed()
            ->where('name', $name)
            ->where(function (Builder $query) use ($brand): void {
                if ($brand === null) {
                    $query->whereNull('brand');

                    return;
                }

                $query->where('brand', $brand);
            })
            ->first();
    }

    protected function syncContainsAllergens(int $productId, array $codes, array $allergenIdMap): array
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
            $link = CommercialProductAllergen::withTrashed()
                ->where('commercial_product_id', $productId)
                ->where('allergen_id', $allergenId)
                ->where('presence_type', AllergenPresenceType::Contains->value)
                ->first();

            if (! $link instanceof CommercialProductAllergen) {
                CommercialProductAllergen::query()->create([
                    'commercial_product_id' => $productId,
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

        $toDeleteQuery = CommercialProductAllergen::query()
            ->where('commercial_product_id', $productId)
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

    protected function rowKey(string $name, ?string $brand): string
    {
        $normalizedName = mb_strtolower(trim($name));
        $normalizedBrand = $brand === null ? '' : mb_strtolower(trim($brand));

        return $normalizedName . '||' . $normalizedBrand;
    }
}

