<?php

declare(strict_types=1);

namespace App\Enums;

enum ProductionItemSourceType: string
{
    case Ingredient = 'ingredient';
    case CommercialProduct = 'commercial_product';

    public static function options(): array
    {
        return [
            self::Ingredient->value => 'Ingrediente',
            self::CommercialProduct->value => 'Prodotto commerciale',
        ];
    }
}
