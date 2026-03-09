<?php

declare(strict_types=1);

namespace App\Filament\Resources\CommercialProducts\RelationManagers;

use App\Enums\AllergenPresenceType;
use App\Models\Allergen;
use App\Models\CommercialProductAllergen;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AllergensRelationManager extends RelationManager
{
    protected static string $relationship = 'allergens';

    protected static ?string $title = 'Allergeni';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label('Codice')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pivot.presence_type')
                    ->label('Presenza')
                    ->badge()
                    ->formatStateUsing(
                        fn (mixed $state): string => AllergenPresenceType::options()[
                            $state instanceof AllergenPresenceType ? $state->value : (string) $state
                        ] ?? (string) $state,
                    ),
            ])
            ->headerActions([
                Action::make('attach_allergen')
                    ->label('Aggiungi allergene')
                    ->form([
                        Select::make('allergen_id')
                            ->label('Allergene')
                            ->options(fn (): array => Allergen::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->required(),
                        Select::make('presence_type')
                            ->label('Presenza')
                            ->options(AllergenPresenceType::options())
                            ->default(AllergenPresenceType::Contains->value)
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $this->attachOrUpdateAllergen(
                            (int) $data['allergen_id'],
                            (string) $data['presence_type'],
                        );
                    }),
            ])
            ->recordActions([
                Action::make('change_presence')
                    ->label('Modifica presenza')
                    ->form([
                        Select::make('presence_type')
                            ->label('Presenza')
                            ->options(AllergenPresenceType::options())
                            ->required(),
                    ])
                    ->fillForm(fn (Allergen $record): array => [
                        'presence_type' => (string) ($record->pivot->presence_type ?? AllergenPresenceType::Contains->value),
                    ])
                    ->action(function (Allergen $record, array $data): void {
                        $this->attachOrUpdateAllergen(
                            (int) $record->id,
                            (string) $data['presence_type'],
                        );
                    }),
                Action::make('remove')
                    ->label('Rimuovi')
                    ->requiresConfirmation()
                    ->action(function (Allergen $record): void {
                        $this->detachAllergen((int) $record->id);
                    }),
            ]);
    }

    protected function attachOrUpdateAllergen(int $allergenId, string $presenceType): void
    {
        $commercialProductId = (int) $this->ownerRecord->id;
        $timestamp = now();

        CommercialProductAllergen::query()
            ->where('commercial_product_id', $commercialProductId)
            ->where('allergen_id', $allergenId)
            ->whereNull('deleted_at')
            ->where('presence_type', '!=', $presenceType)
            ->update([
                'deleted_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

        $existing = CommercialProductAllergen::withTrashed()
            ->where('commercial_product_id', $commercialProductId)
            ->where('allergen_id', $allergenId)
            ->where('presence_type', $presenceType)
            ->first();

        if ($existing !== null) {
            $existing->forceFill([
                'deleted_at' => null,
                'updated_at' => $timestamp,
            ])->save();

            return;
        }

        CommercialProductAllergen::query()->create([
            'commercial_product_id' => $commercialProductId,
            'allergen_id' => $allergenId,
            'presence_type' => $presenceType,
        ]);
    }

    protected function detachAllergen(int $allergenId): void
    {
        $timestamp = now();

        CommercialProductAllergen::query()
            ->where('commercial_product_id', (int) $this->ownerRecord->id)
            ->where('allergen_id', $allergenId)
            ->whereNull('deleted_at')
            ->update([
                'deleted_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
    }
}

