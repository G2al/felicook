<?php

declare(strict_types=1);

namespace App\Enums;

enum IngredientSupplierPriceType: string
{
    case PerUnit = 'per_unit';
    case PerPack = 'per_pack';

    public static function options(): array
    {
        return [
            self::PerUnit->value => 'Per unità',
            self::PerPack->value => 'Per confezione',
        ];
    }
}
