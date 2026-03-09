<?php

declare(strict_types=1);

namespace App\Filament\Resources\CommercialProducts\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NutritionalValuesRelationManager extends RelationManager
{
    protected static string $relationship = 'nutritionalValues';

    protected static ?string $title = 'Valori nutrizionali';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        TextInput::make('energy_kj')->label('Energia (kJ)')->numeric()->minValue(0),
                        TextInput::make('energy_kcal')->label('Energia (kcal)')->numeric()->minValue(0),
                        TextInput::make('fat')->label('Grassi')->numeric()->minValue(0),
                        TextInput::make('saturated_fat')->label('Grassi saturi')->numeric()->minValue(0),
                        TextInput::make('mono_fat')->label('Grassi monoinsaturi')->numeric()->minValue(0),
                        TextInput::make('poly_fat')->label('Grassi polinsaturi')->numeric()->minValue(0),
                        TextInput::make('carbs')->label('Carboidrati')->numeric()->minValue(0),
                        TextInput::make('sugars')->label('Zuccheri')->numeric()->minValue(0),
                        TextInput::make('polyols')->label('Polioli')->numeric()->minValue(0),
                        TextInput::make('erythritol')->label('Eritritolo')->numeric()->minValue(0),
                        TextInput::make('fiber')->label('Fibre')->numeric()->minValue(0),
                        TextInput::make('protein')->label('Proteine')->numeric()->minValue(0),
                        TextInput::make('salt')->label('Sale')->numeric()->minValue(0),
                        TextInput::make('alcohol')->label('Alcol')->numeric()->minValue(0),
                        TextInput::make('water')->label('Acqua')->numeric()->minValue(0),
                        TextInput::make('edible_part_percentage')
                            ->label('Parte edibile (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query->withoutGlobalScopes([SoftDeletingScope::class]),
            )
            ->columns([
                TextColumn::make('energy_kcal')->label('Energia (kcal)')->numeric(decimalPlaces: 2),
                TextColumn::make('fat')->label('Grassi')->numeric(decimalPlaces: 2),
                TextColumn::make('carbs')->label('Carboidrati')->numeric(decimalPlaces: 2),
                TextColumn::make('protein')->label('Proteine')->numeric(decimalPlaces: 2),
                TextColumn::make('salt')->label('Sale')->numeric(decimalPlaces: 2),
                TextColumn::make('edible_part_percentage')->label('Parte edibile (%)')->numeric(decimalPlaces: 2),
                TextColumn::make('updated_at')->label('Aggiornato il')->dateTime()->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nuovo valore nutrizionale')
                    ->visible(fn (): bool => $this->ownerRecord->nutritionalValues()->count() === 0),
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

