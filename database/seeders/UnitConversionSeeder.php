<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\UnitConversion;
use Illuminate\Database\Seeder;

class UnitConversionSeeder extends Seeder
{
    public function run(): void
    {
        $conversions = [
            ['from_unit_code' => 'kg', 'to_unit_code' => 'g', 'multiplier' => 1000],
            ['from_unit_code' => 'l', 'to_unit_code' => 'ml', 'multiplier' => 1000],
        ];

        foreach ($conversions as $conversion) {
            UnitConversion::query()->updateOrCreate(
                [
                    'from_unit_code' => $conversion['from_unit_code'],
                    'to_unit_code' => $conversion['to_unit_code'],
                ],
                ['multiplier' => $conversion['multiplier']],
            );
        }
    }
}
