<?php

namespace App\Filament\Resources\MasterBiayaResource\Pages;

use App\Filament\Resources\MasterBiayaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterBiayas extends ListRecords
{
    protected static string $resource = MasterBiayaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

