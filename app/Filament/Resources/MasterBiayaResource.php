<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MasterBiayaResource\Pages;
use App\Models\MasterBiaya;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MasterBiayaResource extends Resource
{
    protected static ?string $model = MasterBiaya::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Master Biaya';
    protected static ?string $navigationGroup = 'Master';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Master Biaya')
                ->schema([
                    Forms\Components\Select::make('product_id')
                        ->label('Produk')
                        ->options(fn (): array => \App\Models\Product::where('type', 'produk')->orderBy('name')->pluck('name', 'id')->all())
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            $product = $state ? \App\Models\Product::query()->find($state) : null;
                            $set('nama_produk', $product?->name);
                            $set('kode_produk', $product?->sku);
                        }),
                    Forms\Components\TextInput::make('nama_produk')
                        ->required()
                        ->maxLength(255)
                        ->readOnly(),
                    Forms\Components\TextInput::make('kode_produk')
                        ->maxLength(100)
                        ->readOnly(),
                    Forms\Components\Textarea::make('catatan')
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Detail Biaya')
                ->schema([
                    Forms\Components\Repeater::make('details')
                        ->relationship()
                        ->schema([
                            Forms\Components\TextInput::make('nama_biaya')
                                ->label('Nama')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(2),
                            Forms\Components\TextInput::make('harga')
                                ->label('Harga')
                                ->default(0)
                                ->prefix('Rp')
                                ->columnSpan(1)
                                ->numeric(),
                            Forms\Components\Textarea::make('catatan')
                                ->label('Keterangan')
                                ->columnSpanFull(),
                        ])
                        ->columns(3)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            $total = collect($state)->sum(function ($row) {
                                return (float) ($row['harga'] ?? 0);
                            });
                            $set('estimasi_biaya', $total);
                        }),
                ]),

            Forms\Components\Section::make('Total Estimasi')
                ->schema([
                    Forms\Components\TextInput::make('estimasi_biaya')
                        ->label('Estimasi Biaya')
                        ->numeric()
                        ->prefix('Rp')
                        ->readOnly(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.sku')
                    ->label('Kode')
                    ->searchable(),
                Tables\Columns\TextColumn::make('estimasi_biaya')
                    ->label('Estimasi Biaya')
                    ->formatStateUsing(fn ($state): string => 'Rp ' . number_format((float) ($state ?? 0), 0, ',', '.'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
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
            'index' => Pages\ListMasterBiayas::route('/'),
            'create' => Pages\CreateMasterBiaya::route('/create'),
            'edit' => Pages\EditMasterBiaya::route('/{record}/edit'),
        ];
    }
}
