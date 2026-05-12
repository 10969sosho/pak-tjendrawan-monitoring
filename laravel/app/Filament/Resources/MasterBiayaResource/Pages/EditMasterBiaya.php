<?php

namespace App\Filament\Resources\MasterBiayaResource\Pages;

use App\Filament\Resources\MasterBiayaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterBiaya extends EditRecord
{
    protected static string $resource = MasterBiayaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
