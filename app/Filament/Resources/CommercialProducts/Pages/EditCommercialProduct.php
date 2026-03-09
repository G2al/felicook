<?php

declare(strict_types=1);

namespace App\Filament\Resources\CommercialProducts\Pages;

use App\Filament\Resources\CommercialProducts\CommercialProductResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCommercialProduct extends EditRecord
{
    protected static string $resource = CommercialProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}

