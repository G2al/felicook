<?php

declare(strict_types=1);

namespace App\Filament\Resources\Ingredients\Schemas;

use App\Models\Unit;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IngredientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Generale')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nome ingrediente')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('label_name')
                                    ->label('Nome etichetta')
                                    ->maxLength(255),
                                TextInput::make('internal_code')
                                    ->label('Codice interno')
                                    ->maxLength(255),
                                TextInput::make('category')
                                    ->label('Categoria')
                                    ->maxLength(255),
                                Select::make('base_unit_code')
                                    ->label('Unità base')
                                    ->relationship('baseUnit', 'name')
                                    ->getOptionLabelFromRecordUsing(
                                        fn (Unit $record): string => "{$record->name} ({$record->code})",
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('recipe_id')
                                    ->label('Ricetta collegata')
                                    ->relationship('recipe', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),
                        Toggle::make('is_active')
                            ->label('Attivo')
                            ->default(true)
                            ->required(),
                    ]),
                Section::make('Caratteristiche')
                    ->schema([
                        Toggle::make('is_frozen')
                            ->label('Surgelato'),
                        Toggle::make('is_blast_chilled')
                            ->label('Abbattuto'),
                        Toggle::make('is_organic')
                            ->label('Biologico'),
                    ])
                    ->columns(3),
                Section::make('Note')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Note')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
