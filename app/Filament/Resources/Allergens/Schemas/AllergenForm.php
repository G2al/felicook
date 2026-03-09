<?php

declare(strict_types=1);

namespace App\Filament\Resources\Allergens\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AllergenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),
                TextInput::make('code')
                    ->label('Codice')
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
