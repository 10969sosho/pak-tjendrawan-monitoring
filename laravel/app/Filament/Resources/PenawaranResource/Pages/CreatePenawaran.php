<?php

namespace App\Filament\Resources\PenawaranResource\Pages;

use App\Filament\Resources\PenawaranResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePenawaran extends CreateRecord
{
    protected static string $resource = PenawaranResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

