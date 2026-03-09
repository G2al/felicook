<?php

declare(strict_types=1);

namespace App\Filament\Resources\Units\Tables;

use App\Enums\UnitDimension;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Codice')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('dimension')
                    ->label('Dimensione')
                    ->badge()
                    ->formatStateUsing(
                        fn (mixed $state): string => UnitDimension::options()[
                            $state instanceof UnitDimension ? $state->value : (string) $state
                        ] ?? (string) $state,
                    )
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Attiva')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
