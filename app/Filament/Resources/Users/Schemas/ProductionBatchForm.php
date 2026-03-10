<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Services\Productions\ProductionBatchService;
use App\Services\Productions\ProductionLotCodeService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Throwable;

class ProductionBatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dati produzione')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                Select::make('recipe_id')
                                    ->label('Ricetta')
                                    ->relationship(
                                        'recipe',
                                        'name',
                                        modifyQueryUsing: fn (Builder $query): Builder => $query->withoutTrashed(),
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                                        if (blank($state)) {
                                            $set('items', []);
                                            return;
                                        }

                                        $items = app(ProductionBatchService::class)->prepareItemsFromRecipe(
                                            (int) $state,
                                            self::dateString($get('expires_at')),
                                        );
                                        $set('items', $items);

                                        if ((float) ($get('produced_weight') ?? 0) <= 0) {
                                            $payload = self::preview($get);
                                            $set('produced_weight', (float) ($payload['recipe_total_weight'] ?? 0));
                                        }
                                    }),
                                DatePicker::make('production_date')
                                    ->label('Data produzione')
                                    ->required()
                                    ->default(now()->toDateString())
                                    ->native(false)
                                    ->live()
                                    ->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                                        if (! blank($get('lot_code'))) {
                                            return;
                                        }

                                        if (blank($state)) {
                                            return;
                                        }

                                        $set('lot_code', app(ProductionLotCodeService::class)->generate(Carbon::parse($state)));
                                    }),
                                DatePicker::make('expires_at')
                                    ->label('Scadenza prodotto finito')
                                    ->required()
                                    ->native(false)
                                    ->rule('after_or_equal:production_date')
                                    ->live()
                                    ->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                                        $items = $get('items');

                                        if (! is_array($items)) {
                                            return;
                                        }

                                        $updated = [];

                                        foreach ($items as $item) {
                                            if (! is_array($item)) {
                                                continue;
                                            }

                                            if (blank($item['expires_at'] ?? null) && filled($state)) {
                                                $item['expires_at'] = $state;
                                            }

                                            $updated[] = $item;
                                        }

                                        $set('items', $updated);
                                    }),
                                TextInput::make('lot_code')
                                    ->label('Lotto prodotto finito')
                                    ->default(function (Get $get): ?string {
                                        $date = self::dateString($get('production_date')) ?? now()->toDateString();

                                        return app(ProductionLotCodeService::class)->generate(Carbon::parse($date));
                                    })
                                    ->disabled()
                                    ->dehydrated()
                                    ->maxLength(255),
                                TextInput::make('produced_weight')
                                    ->label('Peso prodotto finito (g)')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0.0001)
                                    ->live(),
                                TextInput::make('public_price_per_kg')
                                    ->label('Prezzo pubblico/kg')
                                    ->numeric()
                                    ->minValue(0)
                                    ->live(),
                                TextInput::make('currency')
                                    ->label('Valuta')
                                    ->default('EUR')
                                    ->disabled()
                                    ->dehydrated(),
                            ]),
                        Textarea::make('notes')
                            ->label('Note')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('Materie prime e tracciabilità')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->label('Righe produzione')
                            ->schema([
                                Hidden::make('id'),
                                Hidden::make('source_type'),
                                Hidden::make('ingredient_id'),
                                Hidden::make('commercial_product_id'),
                                Hidden::make('sort_order'),
                                TextInput::make('name_snapshot')
                                    ->label('Materia prima')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required(),
                                TextInput::make('quantity')
                                    ->label('Quantità')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->required(),
                                TextInput::make('unit_code')
                                    ->label('Unità')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required(),
                                TextInput::make('quantity_in_grams')
                                    ->label('Quantità in g')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(),
                                TextInput::make('lot_code')
                                    ->label('Lotto ingrediente/prodotto')
                                    ->required()
                                    ->maxLength(255),
                                DatePicker::make('expires_at')
                                    ->label('Scadenza ingrediente/prodotto')
                                    ->required()
                                    ->native(false),
                            ])
                            ->columns(6)
                            ->defaultItems(0)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->collapsible()
                            ->required(),
                    ]),
                Section::make('Riepilogo')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Placeholder::make('preview_total_cost')
                                    ->label('Costo produzione')
                                    ->content(fn (Get $get): string => self::formattedCurrency(
                                        (float) (self::preview($get)['total_cost'] ?? 0),
                                        (string) (self::preview($get)['currency'] ?? 'EUR'),
                                    )),
                                Placeholder::make('preview_cost_per_kg')
                                    ->label('Costo per kg')
                                    ->content(fn (Get $get): string => self::formattedCurrency(
                                        (float) (self::preview($get)['cost_per_kg'] ?? 0),
                                        (string) (self::preview($get)['currency'] ?? 'EUR'),
                                    )),
                                Placeholder::make('preview_allergens')
                                    ->label('Allergeni')
                                    ->content(fn (Get $get): string => self::allergensText(self::preview($get)['allergens_snapshot'] ?? [])),
                            ]),
                    ]),
            ]);
    }

    protected static function preview(Get $get): array
    {
        try {
            return app(ProductionBatchService::class)->hydratePayload([
                'recipe_id' => $get('recipe_id'),
                'production_date' => $get('production_date'),
                'expires_at' => $get('expires_at'),
                'produced_weight' => $get('produced_weight'),
                'currency' => $get('currency'),
                'items' => is_array($get('items')) ? $get('items') : [],
            ]);
        } catch (Throwable) {
            return [];
        }
    }

    protected static function formattedCurrency(float $amount, string $currency): string
    {
        return number_format($amount, 2, ',', '.') . ' ' . $currency;
    }

    protected static function allergensText(array $snapshot): string
    {
        $contains = array_filter(array_map(
            fn($item) => is_array($item) ? (string) ($item['name'] ?? '') : (string) $item,
            $snapshot['contains'] ?? []
        ));

        $mayContain = array_filter(array_map(
            fn($item) => is_array($item) ? (string) ($item['name'] ?? '') : (string) $item,
            $snapshot['may_contain'] ?? []
        ));

        $parts = [];

        if ($contains !== []) {
            $parts[] = 'Contiene: ' . implode(', ', $contains);
        }

        if ($mayContain !== []) {
            $parts[] = 'Può contenere: ' . implode(', ', $mayContain);
        }

        if ($parts === []) {
            return 'Nessuno';
        }

        return implode(' | ', $parts);
    }

    protected static function dateString(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return $value;
    }
}
