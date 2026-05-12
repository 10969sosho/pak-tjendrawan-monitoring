<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockProducts extends BaseWidget
{
    protected static ?string $heading = 'Barang Stok Menipis';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::whereRaw('current_stock <= min_stock')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama Barang'),
                Tables\Columns\TextColumn::make('sku')->label('SKU'),
                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Stok Saat Ini')
                    ->numeric()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('min_stock')
                    ->label('Stok Minimum')
                    ->numeric(),
                Tables\Columns\TextColumn::make('unit')->label('Satuan'),
            ]);
    }
}
