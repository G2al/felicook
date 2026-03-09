<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use App\Enums\ProductionLabelType;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductionBatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query->withoutGlobalScopes([SoftDeletingScope::class]),
            )
            ->columns([
                TextColumn::make('nome_prodotto')
                    ->label('Prodotto')
                    ->state(fn ($record): string => (string) ($record->recipe_snapshot['name'] ?? $record->recipe?->name ?? 'N/D')),
                TextColumn::make('lot_code')
                    ->label('Lotto')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('production_date')
                    ->label('Produzione')
                    ->date()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->label('Scadenza')
                    ->date()
                    ->sortable(),
                TextColumn::make('produced_weight')
                    ->label('Peso (g)')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('total_cost')
                    ->label('Costo totale')
                    ->money(fn ($record): string => (string) ($record->currency ?? 'EUR'))
                    ->sortable(),
                TextColumn::make('public_price_per_kg')
                    ->label('Prezzo pubblico/kg')
                    ->money(fn ($record): string => (string) ($record->currency ?? 'EUR'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Aggiornato il')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                self::labelAction('etichetta_completa', ProductionLabelType::Completa, Heroicon::OutlinedDocumentText),
                self::labelAction('etichetta_bancone', ProductionLabelType::Bancone, Heroicon::OutlinedTag),
                self::labelAction('etichetta_mini', ProductionLabelType::ConfezioneMini, Heroicon::OutlinedQrCode),
                self::labelAction('etichetta_spedizione', ProductionLabelType::Spedizione, Heroicon::OutlinedTruck),
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function labelAction(
        string $name,
        ProductionLabelType $type,
        string|Heroicon $icon,
    ): Action {
        return Action::make($name)
            ->label($type->label())
            ->icon($icon)
            ->url(fn ($record): string => route('filament.admin.produzioni.etichette.pdf', [
                'record' => $record,
                'template' => $type->value,
            ]))
            ->openUrlInNewTab();
    }
}
