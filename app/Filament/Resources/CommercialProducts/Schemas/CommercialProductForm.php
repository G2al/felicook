<?php

declare(strict_types=1);

namespace App\Filament\Resources\CommercialProducts\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CommercialProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Generale')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nome prodotto')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('brand')
                                    ->label('Brand')
                                    ->maxLength(255),
                                TextInput::make('category')
                                    ->label('Categoria')
                                    ->maxLength(255),
                                Toggle::make('is_active')
                                    ->label('Attivo')
                                    ->default(true)
                                    ->required(),
                            ]),
                        Textarea::make('ingredient_list')
                            ->label('Lista ingredienti')
                            ->rows(6)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

