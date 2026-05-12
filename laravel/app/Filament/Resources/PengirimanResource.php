<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengirimanResource\Pages;
use App\Models\Pengiriman;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PengirimanResource extends Resource
{
    protected static ?string $model = Pengiriman::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';
    protected static ?string $navigationLabel = 'Pengiriman';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Pengiriman')
                ->schema([
                    Forms\Components\Select::make('purchase_order_id')
                        ->label('Purchase Order')
                        ->relationship('purchaseOrder', 'number')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if (!$state) {
                                $set('purchase_order_item_id', null);
                                $set('qty_kirim', 0);
                            }
                        }),
                    Forms\Components\Select::make('purchase_order_item_id')
                        ->label('Item / Produk')
                        ->options(function (Forms\Get $get, $livewire, ?Pengiriman $record) {
                            $poId = $get('purchase_order_id');
                            if (!$poId) return [];
                            
                            $po = PurchaseOrder::with('items.product')->find($poId);
                            if (!$po) return [];
                            
                            $currentItemId = $record ? $record->purchase_order_item_id : null;
                            
                            return $po->items->filter(function ($item) use ($currentItemId) {
                                if ($item->id === $currentItemId) return true;
                                return $item->qty_ready_to_ship > 0;
                            })
                                ->mapWithKeys(fn ($item) => [
                                    $item->id => $item->product->name . ' (Ready: ' . $item->qty_ready_to_ship . ')'
                                ]);
                        })
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get, ?Pengiriman $record) {
                            if (!$state) {
                                $set('qty_kirim', 0);
                                return;
                            }
                            
                            $item = PurchaseOrderItem::find($state);
                            if ($item) {
                                if ($record && $record->purchase_order_item_id === $state) {
                                    $set('qty_kirim', $record->qty_kirim);
                                } else {
                                    $set('qty_kirim', $item->qty_ready_to_ship);
                                }
                            }
                        }),
                    Forms\Components\TextInput::make('qty_kirim')
                        ->label('Qty Kirim')
                        ->numeric()
                        ->minValue(0)
                        ->required()
                        ->rules([
                            function (Forms\Get $get, ?Pengiriman $record) {
                                return function ($attribute, $value, $fail) use ($get, $record) {
                                    $itemId = $get('purchase_order_item_id');
                                    if (!$itemId) return;
                                    
                                    $item = PurchaseOrderItem::find($itemId);
                                    if (!$item) return;
                                    
                                    $maxQty = $item->qty_ready_to_ship;
                                    if ($record && $record->purchase_order_item_id === $itemId) {
                                        $maxQty += $record->qty_kirim;
                                    }
                                    
                                    if ($value > $maxQty) {
                                        $fail("Qty tidak boleh melebihi " . $maxQty);
                                    }
                                };
                            }
                        ]),
                    Forms\Components\DatePicker::make('tanggal_kirim')
                        ->default(now())
                        ->required(),
                    Forms\Components\TextInput::make('penerima')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('tujuan_pengiriman')
                        ->label('Tujuan Pengiriman')
                        ->maxLength(255),
                    Forms\Components\Textarea::make('detail_pengiriman')
                        ->label('Detail Pengiriman')
                        ->columnSpanFull(),
                    Forms\Components\Select::make('status')
                        ->options([
                            'siap_kirim' => 'Belum Kirim',
                            'dikirim' => 'Sudah Kirim',
                            'diterima' => 'Diterima',
                            'done' => 'Done',
                        ])
                        ->default('siap_kirim')
                        ->required(),
                    Forms\Components\Textarea::make('catatan')
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('purchaseOrder.number')
                    ->label('PO')
                    ->searchable(),
                Tables\Columns\TextColumn::make('purchaseOrderItem.product.name')
                    ->label('Item / Produk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_kirim')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('qty_kirim'),
                Tables\Columns\TextColumn::make('penerima')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'siap_kirim',
                        'info' => 'dikirim',
                        'warning' => 'diterima',
                        'success' => 'done',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'siap_kirim' => 'Belum Kirim',
                        'dikirim' => 'Sudah Kirim',
                        'diterima' => 'Diterima',
                        'done' => 'Done',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('changeStatus')
                    ->label('Ubah Status')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Status Pengiriman')
                            ->options([
                                'siap_kirim' => 'Belum Kirim',
                                'dikirim' => 'Sudah Kirim',
                                'diterima' => 'Diterima',
                                'done' => 'Done',
                            ])
                            ->required(),
                    ])
                    ->action(function (array $data, \App\Models\Pengiriman $record): void {
                        $record->update(['status' => $data['status']]);
                    }),
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
            'index' => Pages\ListPengirimans::route('/'),
            'create' => Pages\CreatePengiriman::route('/create'),
            'edit' => Pages\EditPengiriman::route('/{record}/edit'),
        ];
    }
}
