<?php

declare(strict_types=1);

namespace App\Enums;

enum ProductionLabelType: string
{
    case Completa = 'completa';
    case Bancone = 'bancone';
    case ConfezioneMini = 'confezione-mini';
    case Spedizione = 'spedizione';

    public function view(): string
    {
        return match ($this) {
            self::Completa => 'pdf.produzioni.completa',
            self::Bancone => 'pdf.produzioni.bancone',
            self::ConfezioneMini => 'pdf.produzioni.confezione-mini',
            self::Spedizione => 'pdf.produzioni.spedizione',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Completa => 'Etichetta completa',
            self::Bancone => 'Etichetta bancone',
            self::ConfezioneMini => 'Etichetta mini 62x30,48',
            self::Spedizione => 'Etichetta spedizione',
        };
    }
}
