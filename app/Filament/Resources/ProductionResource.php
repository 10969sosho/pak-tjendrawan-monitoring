<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductionResource\Pages;
use App\Models\BarangMasukItem;
use App\Models\Production;
use App\Models\PurchaseOrderItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductionResource extends Resource
{
    protected static ?string $model = Production::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationLabel = 'Produksi';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Produksi')
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->default('PRD-' . date('YmdHis'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->readOnly(),
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal Proses')
                            ->default(now())
                            ->required(),
                        Forms\Components\Select::make('purchase_order_id')
                            ->label('PO')
                            ->relationship('purchaseOrder', 'number', modifyQueryUsing: fn ($query) => $query->where('status', 'process')->orderBy('number'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live(),
                        Forms\Components\Select::make('purchase_order_item_id')
                            ->label('Item PO')
                            ->options(function ($get, $record): array {
                                $poId = $get('purchase_order_id');
                                if (! $poId) {
                                    return [];
                                }

                                return PurchaseOrderItem::query()
                                    ->where('purchase_order_id', $poId)
                                    ->where(function ($query) use ($record) {
                                        $query->whereDoesntHave('productions')
                                              ->orWhereIn('id', [$record?->purchase_order_item_id]);
                                    })
                                    ->with('product')
                                    ->get()
                                    ->mapWithKeys(fn (PurchaseOrderItem $item) => [
                                        $item->id => trim(($item->product?->name ?? '-') . ' (Qty: ' . ((float) ($item->qty ?? 0)) . ')'),
                                    ])
                                    ->all();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $item = $state ? PurchaseOrderItem::with('product')->find($state) : null;
                                $set('satuan_hasil', $item?->product?->unit ?? 'pcs');
                            }),
                        Forms\Components\TextInput::make('qty_produced')
                            ->label('Hasil Produksi (Qty)')
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->default(0),
                        Forms\Components\TextInput::make('satuan_hasil')
                            ->label('Satuan Hasil')
                            ->default('pcs')
                            ->required()
                            ->disabled(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Belum Proses',
                                'completed' => 'Sudah Proses',
                            ])
                            ->required()
                            ->default('draft')
                            ->live(),
                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Barang Masuk yang Dipakai')
                    ->schema([
                        Forms\Components\Repeater::make('materials')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('barang_masuk_item_id')
                                    ->label('Barang Masuk')
                                    ->options(function ($get): array {
                                        $poId = $get('../../purchase_order_id');
                                        if (! $poId) {
                                            return [];
                                        }

                                        return BarangMasukItem::query()
                                            ->where('qc_status', 'lolos')
                                            ->with('product', 'barangMasuk')
                                            ->latest('id')
                                            ->get()
                                            ->mapWithKeys(function (BarangMasukItem $item) {
                                                $usedQty = $item->productionMaterials()->sum('qty_used');
                                                $availableQty = (float) ($item->qty ?? 0) - $usedQty;
                                                $label = ($item->product?->name ?? '-') . ' - Qty Tersedia: ' . $availableQty . ' (' . ($item->product?->unit ?? 'pcs') . ')';
                                                return [$item->id => $label];
                                            })
                                            ->all();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Forms\Components\TextInput::make('qty_used')
                                    ->label('Qty Dipakai')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->required(),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchaseOrder.number')
                    ->label('PO')
                    ->searchable(),
                Tables\Columns\TextColumn::make('purchaseOrderItem.product.name')
                    ->label('Item / Produk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('qty_produced')
                    ->label('Hasil'),
                Tables\Columns\TextColumn::make('satuan_hasil')
                    ->label('Satuan'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'info' => 'draft',
                        'success' => 'completed',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Belum Proses',
                        'completed' => 'Sudah Proses',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Belum Proses',
                        'completed' => 'Sudah Proses',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('changeStatus')
                    ->label('Ubah Status')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Belum Proses',
                                'completed' => 'Sudah Proses',
                            ])
                            ->required(),
                    ])
                    ->action(function (array $data, \App\Models\Production $record): void {
                        $record->update(['status' => $data['status']]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductions::route('/'),
            'create' => Pages\CreateProduction::route('/create'),
            'edit' => Pages\EditProduction::route('/{record}/edit'),
        ];
    }
}
