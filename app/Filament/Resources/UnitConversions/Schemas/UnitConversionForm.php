<?php

declare(strict_types=1);

namespace App\Filament\Resources\UnitConversions\Schemas;

use App\Models\Unit;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UnitConversionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('from_unit_code')
                    ->label('Unità di partenza')
                    ->relationship('fromUnit', 'name')
                    ->getOptionLabelFromRecordUsing(
                        fn (Unit $record): string => "{$record->name} ({$record->code})",
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->different('to_unit_code'),
                Select::make('to_unit_code')
                    ->label('Unità di arrivo')
                    ->relationship('toUnit', 'name')
                    ->getOptionLabelFromRecordUsing(
                        fn (Unit $record): string => "{$record->name} ({$record->code})",
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->different('from_unit_code'),
                TextInput::make('multiplier')
                    ->label('Moltiplicatore')
                    ->numeric()
                    ->required()
                    ->minValue(0.0000000001),
            ]);
    }
}
