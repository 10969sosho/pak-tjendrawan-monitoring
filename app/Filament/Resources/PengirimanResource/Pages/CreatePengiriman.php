<?php

namespace App\Filament\Resources\PengirimanResource\Pages;

use App\Filament\Resources\PengirimanResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePengiriman extends CreateRecord
{
    protected static string $resource = PengirimanResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

