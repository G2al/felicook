<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Ingredients\IngredientJsonImportService;
use Illuminate\Console\Command;

class ImportIngredientsJsonCommand extends Command
{
    protected $signature = 'ingredienti:importa-json
        {path? : Percorso assoluto del file JSON}
        {--dry-run : Valida e normalizza senza scrivere sul database}
        {--show-errors=20 : Numero massimo di errori mostrati a terminale}';

    protected $description = 'Importa ingredienti, allergeni e valori nutrizionali da JSON.';

    public function handle(IngredientJsonImportService $ingredientJsonImportService): int
    {
        $path = $this->argument('path');
        $absolutePath = is_string($path) && $path !== ''
            ? $path
            : base_path('ingredienti_completi.json');

        $dryRun = (bool) $this->option('dry-run');
        $maxErrors = max(1, (int) $this->option('show-errors'));

        $this->line('File: ' . $absolutePath);
        $this->line('Modalita: ' . ($dryRun ? 'DRY-RUN' : 'IMPORT'));

        $result = $ingredientJsonImportService->importFromFile($absolutePath, $dryRun);
        $stats = $result['stats'] ?? [];
        $errors = $result['errors'] ?? [];

        $this->newLine();
        $this->table(
            ['Metrica', 'Valore'],
            [
                ['Totale record JSON', (string) ($stats['total'] ?? 0)],
                ['Normalizzati', (string) ($stats['normalized'] ?? 0)],
                ['Importati', (string) ($stats['imported'] ?? 0)],
                ['Falliti', (string) ($stats['failed'] ?? 0)],
                ['Ingredienti creati', (string) ($stats['ingredients_created'] ?? 0)],
                ['Ingredienti aggiornati', (string) ($stats['ingredients_updated'] ?? 0)],
                ['Ingredienti ripristinati', (string) ($stats['ingredients_restored'] ?? 0)],
                ['Nutrizionali creati', (string) ($stats['nutritional_created'] ?? 0)],
                ['Nutrizionali aggiornati', (string) ($stats['nutritional_updated'] ?? 0)],
                ['Nutrizionali ripristinati', (string) ($stats['nutritional_restored'] ?? 0)],
                ['Link allergeni creati', (string) ($stats['allergen_links_created'] ?? 0)],
                ['Link allergeni ripristinati', (string) ($stats['allergen_links_restored'] ?? 0)],
                ['Link allergeni disattivati', (string) ($stats['allergen_links_deleted'] ?? 0)],
            ],
        );

        if ($errors !== []) {
            $this->newLine();
            $this->warn('Errori rilevati: ' . count($errors));

            foreach (array_slice($errors, 0, $maxErrors) as $error) {
                $row = $error['row'] ?? '?';
                $message = (string) ($error['message'] ?? 'Errore sconosciuto.');
                $this->line(" - Riga {$row}: {$message}");
            }
        }

        if ($errors !== []) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}

