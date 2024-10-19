<?php

namespace App\Filament\Admin\Resources\MetergasResource\Pages;

use App\Filament\Admin\Resources\MetergasResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMetergas extends ManageRecords
{
    protected static string $resource = MetergasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
