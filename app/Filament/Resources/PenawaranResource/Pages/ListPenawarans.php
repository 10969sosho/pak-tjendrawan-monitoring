<?php

namespace App\Filament\Resources\PenawaranResource\Pages;

use App\Filament\Resources\PenawaranResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPenawarans extends ListRecords
{
    protected static string $resource = PenawaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

