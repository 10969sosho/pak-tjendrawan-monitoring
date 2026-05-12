<?php

namespace App\Filament\Widgets;

use App\Models\StockMovement;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestActivities extends BaseWidget
{
    protected static ?string $heading = 'Aktivitas Terbaru';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                StockMovement::latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipe')
                    ->colors([
                        'success' => 'in',
                        'danger' => 'out',
                    ])
                    ->formatStateUsing(fn (string $state): string => $state === 'in' ? 'Masuk' : 'Keluar'),
                Tables\Columns\TextColumn::make('qty')
                    ->label('Jumlah')
                    ->numeric(),
                Tables\Columns\TextColumn::make('reference')
                    ->label('Referensi'),
            ]);
    }
}
