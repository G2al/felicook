<?php

declare(strict_types=1);

namespace App\Filament\Resources\Recipes\Schemas;

use App\Models\Unit;
use App\Services\Recipes\RecipeFoodCostService;
use App\Services\Recipes\RecipeIngredientSourceService;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Throwable;

class RecipeForm
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
                                    ->label('Nome ricetta')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('category')
                                    ->label('Categoria')
                                    ->maxLength(255),
                                TextInput::make('portions')
                                    ->label('Porzioni')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(1)
                                    ->live(),
                                TextInput::make('yield_percentage')
                                    ->label('Resa (%)')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0.01)
                                    ->maxValue(100)
                                    ->default(100)
                                    ->live(),
                                TextInput::make('total_weight')
                                    ->label('Peso totale (g)')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(false),
                                Toggle::make('is_active')
                                    ->label('Attiva')
                                    ->default(true)
                                    ->required(),
                            ]),
                        Textarea::make('description')
                            ->label('Descrizione')
                            ->rows(6)
                            ->columnSpanFull(),
                    ]),
                Section::make('Materie prime')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('recipeIngredients')
                            ->label('Componenti ricetta')
                            ->relationship('recipeIngredients')
                            ->schema([
                                Hidden::make('ingredient_id'),
                                Hidden::make('commercial_product_id'),
                                Select::make('source_key')
                                    ->label('Materia prima')
                                    ->dehydrated(false)
                                    ->required()
                                    ->searchable()
                                    ->getSearchResultsUsing(
                                        fn (string $search): array => app(RecipeIngredientSourceService::class)->search($search),
                                    )
                                    ->getOptionLabelUsing(
                                        fn ($value): ?string => app(RecipeIngredientSourceService::class)->label(is_string($value) ? $value : null),
                                    )
                                    ->live()
                                    ->afterStateHydrated(function (?string $state, Get $get, Set $set): void {
                                        if (filled($state)) {
                                            return;
                                        }

                                        $sourceKey = app(RecipeIngredientSourceService::class)->keyFromIds(
                                            ($get('ingredient_id') !== null ? (int) $get('ingredient_id') : null),
                                            ($get('commercial_product_id') !== null ? (int) $get('commercial_product_id') : null),
                                        );

                                        if ($sourceKey !== null) {
                                            $set('source_key', $sourceKey);
                                        }
                                    })
                                    ->afterStateUpdated(function (?string $state, Set $set): void {
                                        $resolved = app(RecipeIngredientSourceService::class)->resolve($state);
                                        $set('ingredient_id', $resolved['ingredient_id']);
                                        $set('commercial_product_id', $resolved['commercial_product_id']);

                                        if (filled($resolved['default_unit_code'])) {
                                            $set('unit_code', (string) $resolved['default_unit_code']);
                                        }
                                    }),
                                TextInput::make('quantity')
                                    ->label('Quantita')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0.0001)
                                    ->live(),
                                Select::make('unit_code')
                                    ->label('Unita')
                                    ->options(fn (): array => Unit::query()->where('is_active', true)->orderBy('name')->pluck('name', 'code')->all())
                                    ->default('g')
                                    ->searchable()
                                    ->required()
                                    ->live(),
                                TextInput::make('sort_order')
                                    ->label('Ordine')
                                    ->numeric()
                                    ->integer()
                                    ->default(0)
                                    ->minValue(0),
                            ])
                            ->columns(4)
                            ->orderColumn('sort_order')
                            ->defaultItems(0)
                            ->collapsible(),
                    ]),
                Section::make('Anteprima live')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Placeholder::make('preview_food_cost')
                                    ->label('Costo alimentare')
                                    ->content(fn (Get $get): string => self::formatFoodCostPreview($get)),
                                Placeholder::make('preview_total_weight')
                                    ->label('Peso totale')
                                    ->content(fn (Get $get): string => self::formatTotalWeightPreview($get)),
                            ]),
                    ]),
            ]);
    }

    protected static function formatFoodCostPreview(Get $get): string
    {
        $result = self::calculatePreview($get);

        if ($result === null) {
            return '0,00 EUR';
        }

        return number_format((float) ($result['total_cost'] ?? 0), 2, ',', '.') . ' ' . (string) ($result['currency'] ?? 'EUR');
    }

    protected static function formatTotalWeightPreview(Get $get): string
    {
        $result = self::calculatePreview($get);

        if ($result === null) {
            return '0,00 g';
        }

        return number_format((float) ($result['total_weight'] ?? 0), 2, ',', '.') . ' g';
    }

    protected static function calculatePreview(Get $get): ?array
    {
        $rows = $get('recipeIngredients');

        if (! is_array($rows) || $rows === []) {
            return [
                'total_cost' => 0.0,
                'total_weight' => 0.0,
                'currency' => 'EUR',
            ];
        }

        try {
            return app(RecipeFoodCostService::class)->calculateFromFormState(
                $rows,
                (float) ($get('yield_percentage') ?? 100),
                (int) ($get('portions') ?? 1),
            );
        } catch (Throwable) {
            return null;
        }
    }
}
