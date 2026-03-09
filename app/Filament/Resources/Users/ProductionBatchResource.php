<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateProductionBatch;
use App\Filament\Resources\Users\Pages\EditProductionBatch;
use App\Filament\Resources\Users\Pages\ListProductionBatches;
use App\Filament\Resources\Users\Schemas\ProductionBatchForm;
use App\Filament\Resources\Users\Tables\ProductionBatchesTable;
use App\Models\ProductionBatch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductionBatchResource extends Resource
{
    protected static ?string $model = ProductionBatch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'Area produzione';

    protected static ?string $navigationLabel = 'Produzioni';

    protected static ?string $modelLabel = 'produzione';

    protected static ?string $pluralModelLabel = 'produzioni';

    protected static ?string $slug = 'produzioni';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return ProductionBatchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductionBatchesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductionBatches::route('/'),
            'create' => CreateProductionBatch::route('/create'),
            'edit' => EditProductionBatch::route('/{record}/edit'),
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
