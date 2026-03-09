<?php

declare(strict_types=1);

namespace App\Filament\Resources\UnitConversions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UnitConversionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fromUnit.code')
                    ->label('Da unità')
                    ->sortable(),
                TextColumn::make('toUnit.code')
                    ->label('A unità')
                    ->sortable(),
                TextColumn::make('multiplier')
                    ->label('Moltiplicatore')
                    ->numeric(decimalPlaces: 10)
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
