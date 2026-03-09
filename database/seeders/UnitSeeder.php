<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UnitDimension;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['code' => 'g', 'name' => 'Grammo', 'dimension' => UnitDimension::Mass->value, 'is_active' => true],
            ['code' => 'kg', 'name' => 'Chilogrammo', 'dimension' => UnitDimension::Mass->value, 'is_active' => true],
            ['code' => 'ml', 'name' => 'Millilitro', 'dimension' => UnitDimension::Volume->value, 'is_active' => true],
            ['code' => 'l', 'name' => 'Litro', 'dimension' => UnitDimension::Volume->value, 'is_active' => true],
            ['code' => 'pz', 'name' => 'Pezzo', 'dimension' => UnitDimension::Each->value, 'is_active' => true],
        ];

        foreach ($units as $unit) {
            Unit::query()->updateOrCreate(
                ['code' => $unit['code']],
                [
                    'name' => $unit['name'],
                    'dimension' => $unit['dimension'],
                    'is_active' => $unit['is_active'],
                ],
            );
        }
    }
}
