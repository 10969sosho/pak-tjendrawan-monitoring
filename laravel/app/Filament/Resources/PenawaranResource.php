<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenawaranResource\Pages;
use App\Models\MasterBiaya;
use App\Models\Penawaran;
use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PenawaranResource extends Resource
{
    protected static ?string $model = Penawaran::class;
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Penawaran';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Penawaran')
                ->schema([
                    Forms\Components\TextInput::make('nomor_penawaran')
                        ->default('QTN-' . now()->format('YmdHis'))
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->readOnly(),
                    Forms\Components\DatePicker::make('tanggal')
                        ->default(now())
                        ->required(),
                    Forms\Components\Select::make('customer_id')
                        ->label('Customer')
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('master_biaya_id')
                        ->label('Master Biaya')
                        ->options(fn () => MasterBiaya::query()->orderBy('nama_produk')->pluck('nama_produk', 'id')->all())
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            $mb = $state ? MasterBiaya::find($state) : null;
                            if ($mb) {
                                $set('nama_produk', $mb->nama_produk);
                                $set('estimasi_biaya', $mb->estimasi_biaya);
                            }
                        }),
                    Forms\Components\TextInput::make('nama_produk')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('qty')
                        ->numeric()
                        ->default(0)
                        ->required(),
                    Forms\Components\TextInput::make('harga_penawaran')
                        ->label('Harga Penawaran (Customer)')
                        ->numeric()
                        ->prefix('Rp')
                        ->default(0)
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, $get, Forms\Set $set) {
                            $set('estimasi_profit', ((float) $state) - ((float) ($get('estimasi_biaya') ?? 0)));
                        }),
                    Forms\Components\TextInput::make('estimasi_biaya')
                        ->numeric()
                        ->prefix('Rp')
                        ->readOnly(),
                    Forms\Components\TextInput::make('estimasi_profit')
                        ->numeric()
                        ->prefix('Rp')
                        ->readOnly(),
                    Forms\Components\Select::make('status')
                        ->options([
                            'draft' => 'Draft',
                            'deal' => 'Deal',
                            'reject' => 'Reject',
                        ])
                        ->default('draft')
                        ->required(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_penawaran')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_produk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('qty')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('harga_penawaran')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('estimasi_profit')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'deal',
                        'danger' => 'reject',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('buat_po')
                    ->label('Buat PO')
                    ->icon('heroicon-o-shopping-cart')
                    ->color('success')
                    ->visible(fn (Penawaran $record) => $record->status === 'deal' && $record->purchaseOrders()->count() === 0)
                    ->action(function (Penawaran $record) {
                        $customer = $record->customer;
                        $po = PurchaseOrder::create([
                            'customer_id' => $customer?->id,
                            'penawaran_id' => $record->id,
                            'number' => 'PO-' . now()->format('YmdHis'),
                            'date' => now(),
                            'supplier_customer' => $customer?->name ?? '-',
                            'nama_produk' => $record->nama_produk,
                            'qty_order' => $record->qty,
                            'harga_deal' => $record->harga_penawaran,
                            'total_amount' => $record->harga_penawaran,
                            'status' => 'draft',
                            'stage' => 'deal',
                        ]);
                        $po->refreshStage();
                    }),
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
            'index' => Pages\ListPenawarans::route('/'),
            'create' => Pages\CreatePenawaran::route('/create'),
            'edit' => Pages\EditPenawaran::route('/{record}/edit'),
        ];
    }
}
