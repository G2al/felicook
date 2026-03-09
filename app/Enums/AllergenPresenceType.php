<?php

declare(strict_types=1);

namespace App\Enums;

enum AllergenPresenceType: string
{
    case Contains = 'contains';
    case MayContain = 'may_contain';

    public static function options(): array
    {
        return [
            self::Contains->value => 'Contiene',
            self::MayContain->value => 'Può contenere',
        ];
    }
}
