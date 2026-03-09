<?php

declare(strict_types=1);

namespace App\Filament\Resources\UnitConversions;

use App\Filament\Resources\UnitConversions\Pages\CreateUnitConversion;
use App\Filament\Resources\UnitConversions\Pages\EditUnitConversion;
use App\Filament\Resources\UnitConversions\Pages\ListUnitConversions;
use App\Filament\Resources\UnitConversions\Schemas\UnitConversionForm;
use App\Filament\Resources\UnitConversions\Tables\UnitConversionsTable;
use App\Models\UnitConversion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UnitConversionResource extends Resource
{
    protected static ?string $model = UnitConversion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static string|\UnitEnum|null $navigationGroup = 'Extra';

    protected static ?string $navigationLabel = 'Conversioni unita';

    protected static ?string $modelLabel = 'conversione unita';

    protected static ?string $pluralModelLabel = 'conversioni unita';

    protected static ?string $slug = 'conversioni-unita';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return UnitConversionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UnitConversionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUnitConversions::route('/'),
            'create' => CreateUnitConversion::route('/create'),
            'edit' => EditUnitConversion::route('/{record}/edit'),
        ];
    }
}
