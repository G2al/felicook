<?php

declare(strict_types=1);

namespace App\Enums;

enum UnitDimension: string
{
    case Mass = 'mass';
    case Volume = 'volume';
    case Each = 'each';

    public static function options(): array
    {
        return [
            self::Mass->value => 'Massa',
            self::Volume->value => 'Volume',
            self::Each->value => 'Pezzi',
        ];
    }
}
