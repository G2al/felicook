<?php

declare(strict_types=1);

namespace App\Filament\Resources\CommercialProducts\Pages;

use App\Filament\Resources\CommercialProducts\CommercialProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCommercialProducts extends ListRecords
{
    protected static string $resource = CommercialProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

