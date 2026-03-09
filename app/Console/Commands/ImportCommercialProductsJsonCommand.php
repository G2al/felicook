<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\CommercialProducts\CommercialProductJsonImportService;
use Illuminate\Console\Command;

class ImportCommercialProductsJsonCommand extends Command
{
    protected $signature = 'prodotti-commerciali:importa-json
        {path? : Percorso assoluto del file JSON}
        {--dry-run : Valida e normalizza senza scrivere sul database}
        {--show-errors=20 : Numero massimo di errori mostrati a terminale}';

    protected $description = 'Importa prodotti commerciali, allergeni e valori nutrizionali da JSON.';

    public function handle(CommercialProductJsonImportService $commercialProductJsonImportService): int
    {
        $path = $this->argument('path');
        $absolutePath = is_string($path) && $path !== ''
            ? $path
            : base_path('prodotti_generali_completi.json');

        $dryRun = (bool) $this->option('dry-run');
        $maxErrors = max(1, (int) $this->option('show-errors'));

        $this->line('File: ' . $absolutePath);
        $this->line('Modalita: ' . ($dryRun ? 'DRY-RUN' : 'IMPORT'));

        $result = $commercialProductJsonImportService->importFromFile($absolutePath, $dryRun);
        $stats = $result['stats'] ?? [];
        $errors = $result['errors'] ?? [];

        $this->newLine();
        $this->table(
            ['Metrica', 'Valore'],
            [
                ['Totale record JSON', (string) ($stats['total'] ?? 0)],
                ['Normalizzati', (string) ($stats['normalized'] ?? 0)],
                ['Duplicati unificati', (string) ($stats['duplicates_merged'] ?? 0)],
                ['Importati', (string) ($stats['imported'] ?? 0)],
                ['Falliti', (string) ($stats['failed'] ?? 0)],
                ['Prodotti creati', (string) ($stats['products_created'] ?? 0)],
                ['Prodotti aggiornati', (string) ($stats['products_updated'] ?? 0)],
                ['Prodotti ripristinati', (string) ($stats['products_restored'] ?? 0)],
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

