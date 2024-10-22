<?php

namespace App\Filament\Admin\Resources\RegencyResource\Pages;

use App\Filament\Admin\Resources\RegencyResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageRegencies extends ManageRecords
{
    protected static string $resource = RegencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
