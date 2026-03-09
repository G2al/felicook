<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ProductionLabelType;
use App\Models\ProductionBatch;
use App\Services\Productions\ProductionLabelPdfService;
use Illuminate\Http\Response;

class ProductionLabelPdfController extends Controller
{
    public function __invoke(
        int|string $record,
        string $template,
        ProductionLabelPdfService $productionLabelPdfService,
    ): Response {
        $productionBatch = ProductionBatch::query()
            ->withTrashed()
            ->findOrFail((int) $record);
        $type = ProductionLabelType::tryFrom($template) ?? ProductionLabelType::Completa;

        return $productionLabelPdfService->stream($productionBatch, $type);
    }
}
