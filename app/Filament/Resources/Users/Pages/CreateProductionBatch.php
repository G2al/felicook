<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\ProductionBatchResource;
use App\Services\Productions\ProductionBatchService;
use Filament\Resources\Pages\CreateRecord;

class CreateProductionBatch extends CreateRecord
{
    protected static string $resource = ProductionBatchResource::class;

    protected array $itemsPayload = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $payload = app(ProductionBatchService::class)->hydratePayload($data);
        $this->itemsPayload = $this->normalizeItemsPayload($payload['items'] ?? []);
        unset($payload['items']);

        return $payload;
    }

    protected function afterCreate(): void
    {
        if ($this->itemsPayload === []) {
            return;
        }

        $this->getRecord()->items()->createMany($this->itemsPayload);
    }

    protected function normalizeItemsPayload(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            unset($item['id']);
            $normalized[] = $item;
        }

        return $normalized;
    }
}
