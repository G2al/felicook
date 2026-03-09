<?php

declare(strict_types=1);

namespace App\Filament\Resources\Ingredients\RelationManagers;

use App\Enums\IngredientSupplierPriceType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IngredientSuppliersRelationManager extends RelationManager
{
    protected static string $relationship = 'ingredientSuppliers';

    protected static ?string $title = 'Fornitori e prezzi';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('supplier_id')
                    ->label('Fornitore')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('price')
                    ->label('Prezzo')
                    ->numeric()
                    ->required()
                    ->minValue(0),
                TextInput::make('currency')
                    ->label('Valuta')
                    ->default('EUR')
                    ->required()
                    ->length(3)
                    ->dehydrateStateUsing(fn (?string $state): string => strtoupper(trim((string) $state))),
                Select::make('price_type')
                    ->label('Tipo prezzo')
                    ->options(IngredientSupplierPriceType::options())
                    ->default(IngredientSupplierPriceType::PerUnit->value)
                    ->live()
                    ->required(),
                Select::make('unit_code')
                    ->label('Unità prezzo')
                    ->relationship('unit', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn (Get $get): bool => $get('price_type') === IngredientSupplierPriceType::PerUnit->value)
                    ->required(fn (Get $get): bool => $get('price_type') === IngredientSupplierPriceType::PerUnit->value),
                TextInput::make('pack_quantity')
                    ->label('Quantità confezione')
                    ->numeric()
                    ->minValue(0.0001)
                    ->visible(fn (Get $get): bool => $get('price_type') === IngredientSupplierPriceType::PerPack->value)
                    ->required(fn (Get $get): bool => $get('price_type') === IngredientSupplierPriceType::PerPack->value),
                Select::make('pack_unit_code')
                    ->label('Unità confezione')
                    ->relationship('packUnit', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn (Get $get): bool => $get('price_type') === IngredientSupplierPriceType::PerPack->value)
                    ->required(fn (Get $get): bool => $get('price_type') === IngredientSupplierPriceType::PerPack->value),
                DatePicker::make('valid_from')
                    ->label('Valido dal'),
                DatePicker::make('valid_to')
                    ->label('Valido al')
                    ->afterOrEqual('valid_from'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query->withoutGlobalScopes([SoftDeletingScope::class]),
            )
            ->columns([
                TextColumn::make('supplier.name')
                    ->label('Fornitore')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Prezzo')
                    ->numeric(decimalPlaces: 4)
                    ->sortable(),
                TextColumn::make('currency')
                    ->label('Valuta')
                    ->sortable(),
                TextColumn::make('price_type')
                    ->label('Tipo prezzo')
                    ->badge()
                    ->formatStateUsing(
                        fn (mixed $state): string => IngredientSupplierPriceType::options()[
                            $state instanceof IngredientSupplierPriceType ? $state->value : (string) $state
                        ] ?? (string) $state,
                    ),
                TextColumn::make('unit_code')
                    ->label('Unità prezzo')
                    ->toggleable(),
                TextColumn::make('pack_quantity')
                    ->label('Quantità confezione')
                    ->numeric(decimalPlaces: 4)
                    ->toggleable(),
                TextColumn::make('pack_unit_code')
                    ->label('Unità confezione')
                    ->toggleable(),
                TextColumn::make('valid_from')
                    ->label('Valido dal')
                    ->date()
                    ->sortable(),
                TextColumn::make('valid_to')
                    ->label('Valido al')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nuovo prezzo'),
            ])
            ->recordActions([
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
}
