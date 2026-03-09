<?php

declare(strict_types=1);

namespace App\Filament\Resources\CommercialProducts\Pages;

use App\Filament\Resources\CommercialProducts\CommercialProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCommercialProduct extends CreateRecord
{
    protected static string $resource = CommercialProductResource::class;
}

