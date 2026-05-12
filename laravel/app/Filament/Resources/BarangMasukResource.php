<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangMasukResource\Pages;
use App\Models\BarangMasuk;
use App\Models\Product;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BarangMasukResource extends Resource
{
    protected static ?string $model = BarangMasuk::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Barang Masuk';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Barang Masuk')
                ->schema([
                    Forms\Components\Select::make('purchase_order_id')
                        ->label('PO')
                        ->relationship('purchaseOrder', 'number')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\DatePicker::make('tanggal_masuk')
                        ->default(now())
                        ->required(),
                    Forms\Components\Textarea::make('catatan')
                        ->label('Catatan')
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Item Barang Masuk')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship()
                        ->schema([
                            Forms\Components\Select::make('product_id')
                                ->label('Barang / Bahan')
                                ->options(fn (): array => Product::where('type', 'bahan_baku')->orderBy('name')->pluck('name', 'id')->all())
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    $product = $state ? Product::query()->find($state) : null;
                                    $set('unit', $product?->unit);
                                }),
                            Forms\Components\TextInput::make('qty')
                                ->label('Qty')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required(),
                            Forms\Components\Select::make('unit')
                                ->label('Satuan')
                                ->options(fn (): array => Unit::query()->orderBy('name')->pluck('name', 'name')->all())
                                ->searchable()
                                ->preload(),
                            Forms\Components\Select::make('qc_status')
                                ->label('Status QC')
                                ->options([
                                    'pending' => 'Pending',
                                    'lolos' => 'Lolos QC',
                                    'reject' => 'Reject QC',
                                ])
                                ->default('pending')
                                ->required(),
                            Forms\Components\Textarea::make('qc_catatan')
                                ->label('Catatan QC')
                                ->columnSpanFull(),
                            Forms\Components\Textarea::make('catatan')
                                ->label('Catatan')
                                ->columnSpanFull(),
                        ])
                        ->columns(4)
                        ->defaultItems(1),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('purchaseOrder.number')
                    ->label('PO')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_masuk')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Item')
                    ->counts('items')
                    ->sortable(),
                Tables\Columns\TextColumn::make('items_summary')
                    ->label('Barang')
                    ->getStateUsing(fn (BarangMasuk $record): string => $record->items->pluck('product.name')->filter()->join(', '))
                    ->wrap(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBarangMasuks::route('/'),
            'create' => Pages\CreateBarangMasuk::route('/create'),
            'edit' => Pages\EditBarangMasuk::route('/{record}/edit'),
        ];
    }
}
