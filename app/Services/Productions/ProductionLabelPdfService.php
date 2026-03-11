<?php

declare(strict_types=1);

namespace App\Services\Productions;

use App\Enums\ProductionLabelType;
use App\Models\ProductionBatch;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ProductionLabelPdfService
{
    public function __construct(
        protected ProductionLabelDataService $productionLabelDataService,
    ) {}

    public function stream(ProductionBatch $productionBatch, ProductionLabelType $type): Response
    {
        $data = $this->productionLabelDataService->build($productionBatch);

        $view = $type->view();

        $fileName = sprintf(
            '%s-%s-%s.pdf',
            Str::slug($type->label(), '-'),
            Str::slug((string) ($data['nome_prodotto'] ?? 'prodotto'), '-'),
            Str::slug((string) ($data['lotto'] ?? 'lotto'), '-'),
        );

        return Pdf::loadView($view, $data)
            ->setPaper($this->paper($type))
            ->stream($fileName);
    }

    protected function paper(ProductionLabelType $type): array
    {
        return match ($type) {

            ProductionLabelType::Completa => [0,0,595.28,841.89],

            // 103mm x 164mm
            ProductionLabelType::Bancone => [0,0,292.17,465.89],

            // 60mm x 60mm
            ProductionLabelType::ConfezioneMini => [0,0,171,171],

            ProductionLabelType::Spedizione => [0,0,283.46,198.43],

        };
    }
}