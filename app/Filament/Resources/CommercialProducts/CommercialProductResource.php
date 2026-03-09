<?php

declare(strict_types=1);

namespace App\Filament\Resources\CommercialProducts;

use App\Filament\Resources\CommercialProducts\Pages\CreateCommercialProduct;
use App\Filament\Resources\CommercialProducts\Pages\EditCommercialProduct;
use App\Filament\Resources\CommercialProducts\Pages\ListCommercialProducts;
use App\Filament\Resources\CommercialProducts\RelationManagers\AllergensRelationManager;
use App\Filament\Resources\CommercialProducts\RelationManagers\NutritionalValuesRelationManager;
use App\Filament\Resources\CommercialProducts\Schemas\CommercialProductForm;
use App\Filament\Resources\CommercialProducts\Tables\CommercialProductsTable;
use App\Models\CommercialProduct;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CommercialProductResource extends Resource
{
    protected static ?string $model = CommercialProduct::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static string|\UnitEnum|null $navigationGroup = 'Inventario';

    protected static ?string $navigationLabel = 'Prodotti commerciali';

    protected static ?string $modelLabel = 'prodotto commerciale';

    protected static ?string $pluralModelLabel = 'prodotti commerciali';

    protected static ?string $slug = 'prodotti-commerciali';

    protected static ?int $navigationSort = 15;

    public static function form(Schema $schema): Schema
    {
        return CommercialProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommercialProductsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AllergensRelationManager::class,
            NutritionalValuesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCommercialProducts::route('/'),
            'create' => CreateCommercialProduct::route('/create'),
            'edit' => EditCommercialProduct::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

