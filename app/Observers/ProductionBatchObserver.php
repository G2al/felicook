<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\ProductionBatch;
use App\Services\Productions\ProductionLotCodeService;
use Carbon\Carbon;

class ProductionBatchObserver
{
    public function __construct(
        protected ProductionLotCodeService $productionLotCodeService,
    ) {}

    public function creating(ProductionBatch $productionBatch): void
    {
        if ($productionBatch->production_date === null) {
            $productionBatch->production_date = now()->toDateString();
        }

        if (blank($productionBatch->lot_code)) {
            $productionBatch->lot_code = $this->productionLotCodeService->generate(
                Carbon::parse((string) $productionBatch->production_date),
            );
        }
    }
}
