<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Allergen;
use Illuminate\Database\Seeder;

class AllergenSeeder extends Seeder
{
    public function run(): void
    {
        $allergens = [
            ['code' => 'GLUTINE', 'name' => 'Cereali contenenti glutine'],
            ['code' => 'CROSTACEI', 'name' => 'Crostacei'],
            ['code' => 'UOVA', 'name' => 'Uova'],
            ['code' => 'PESCE', 'name' => 'Pesce'],
            ['code' => 'ARACHIDI', 'name' => 'Arachidi'],
            ['code' => 'SOIA', 'name' => 'Soia'],
            ['code' => 'LATTE', 'name' => 'Latte'],
            ['code' => 'FRUTTA_GUSCIO', 'name' => 'Frutta a guscio'],
            ['code' => 'SEDANO', 'name' => 'Sedano'],
            ['code' => 'SENAPE', 'name' => 'Senape'],
            ['code' => 'SESAMO', 'name' => 'Semi di sesamo'],
            ['code' => 'SOLFITI', 'name' => 'Anidride solforosa e solfiti'],
            ['code' => 'LUPINI', 'name' => 'Lupini'],
            ['code' => 'MOLLUSCHI', 'name' => 'Molluschi'],
        ];

        foreach ($allergens as $allergen) {
            Allergen::query()->updateOrCreate(
                ['code' => $allergen['code']],
                ['name' => $allergen['name']],
            );
        }
    }
}
