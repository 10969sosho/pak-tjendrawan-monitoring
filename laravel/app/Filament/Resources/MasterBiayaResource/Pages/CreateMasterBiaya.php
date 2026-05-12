<?php

namespace App\Filament\Resources\MasterBiayaResource\Pages;

use App\Filament\Resources\MasterBiayaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMasterBiaya extends CreateRecord
{
    protected static string $resource = MasterBiayaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
