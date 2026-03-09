<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Enums\ProductionLabelType;
use App\Filament\Resources\Users\ProductionBatchResource;
use App\Services\Productions\ProductionBatchService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditProductionBatch extends EditRecord
{
    protected static string $resource = ProductionBatchResource::class;

    protected array $itemsPayload = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['items'] = $this->getRecord()
            ->items()
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($item): array => [
                'id' => $item->id,
                'source_type' => $item->source_type?->value ?? (string) $item->source_type,
                'ingredient_id' => $item->ingredient_id,
                'commercial_product_id' => $item->commercial_product_id,
                'name_snapshot' => $item->name_snapshot,
                'quantity' => (float) $item->quantity,
                'unit_code' => $item->unit_code,
                'quantity_in_grams' => $item->quantity_in_grams !== null ? (float) $item->quantity_in_grams : null,
                'lot_code' => $item->lot_code,
                'expires_at' => $item->expires_at?->toDateString(),
                'sort_order' => $item->sort_order,
                'meta' => is_array($item->meta) ? $item->meta : [],
            ])
            ->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $payload = app(ProductionBatchService::class)->hydratePayload($data, $this->getRecord());
        $this->itemsPayload = $this->normalizeItemsPayload($payload['items'] ?? []);
        unset($payload['items']);

        return $payload;
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();
        $persistedIds = [];

        foreach ($this->itemsPayload as $item) {
            $itemId = isset($item['id']) ? (int) $item['id'] : null;

            if (($itemId ?? 0) > 0) {
                $existing = $record->items()->whereKey($itemId)->first();

                if ($existing !== null) {
                    unset($item['id']);
                    $existing->update($item);
                    $persistedIds[] = $existing->id;

                    continue;
                }
            }

            unset($item['id']);
            $created = $record->items()->create($item);
            $persistedIds[] = $created->id;
        }

        $record->items()
            ->when($persistedIds !== [], fn ($query) => $query->whereNotIn('id', $persistedIds))
            ->delete();
    }

    protected function normalizeItemsPayload(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $normalized[] = $item;
        }

        return $normalized;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('etichetta_completa')
                ->label('Etichetta completa')
                ->icon(Heroicon::OutlinedDocumentText)
                ->url(fn (): string => $this->labelUrl(ProductionLabelType::Completa))
                ->openUrlInNewTab(),
            Action::make('etichetta_bancone')
                ->label('Etichetta bancone')
                ->icon(Heroicon::OutlinedTag)
                ->url(fn (): string => $this->labelUrl(ProductionLabelType::Bancone))
                ->openUrlInNewTab(),
            Action::make('etichetta_mini')
                ->label('Etichetta mini 62x30,48')
                ->icon(Heroicon::OutlinedQrCode)
                ->url(fn (): string => $this->labelUrl(ProductionLabelType::ConfezioneMini))
                ->openUrlInNewTab(),
            Action::make('etichetta_spedizione')
                ->label('Etichetta spedizione')
                ->icon(Heroicon::OutlinedTruck)
                ->url(fn (): string => $this->labelUrl(ProductionLabelType::Spedizione))
                ->openUrlInNewTab(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function labelUrl(ProductionLabelType $type): string
    {
        return route('filament.admin.produzioni.etichette.pdf', [
            'record' => $this->getRecord(),
            'template' => $type->value,
        ]);
    }
}
