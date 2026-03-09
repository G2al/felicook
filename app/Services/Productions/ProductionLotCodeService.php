<?php

declare(strict_types=1);

namespace App\Services\Productions;

use App\Models\ProductionBatch;
use Carbon\CarbonInterface;

class ProductionLotCodeService
{
    public function generate(CarbonInterface $productionDate): string
    {
        $base = $productionDate->format('Ymd');
        $sequence = ProductionBatch::withTrashed()
            ->whereDate('production_date', $productionDate->toDateString())
            ->count() + 1;

        do {
            $lotCode = sprintf('LOT-%s-%04d', $base, $sequence);
            $sequence++;
        } while (
            ProductionBatch::withTrashed()
                ->where('lot_code', $lotCode)
                ->exists()
        );

        return $lotCode;
    }
}
