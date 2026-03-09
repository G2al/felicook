<?php

declare(strict_types=1);

namespace App\Filament\Resources\Units\Schemas;

use App\Enums\UnitDimension;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Codice')
                    ->required()
                    ->maxLength(255)
                    ->disabledOn('edit'),
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),
                Select::make('dimension')
                    ->label('Dimensione')
                    ->options(UnitDimension::options())
                    ->required(),
                Toggle::make('is_active')
                    ->label('Attiva')
                    ->default(true)
                    ->required(),
            ]);
    }
}
