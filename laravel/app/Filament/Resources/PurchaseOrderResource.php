<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'PO Customer';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('PO')
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->default('PO-' . date('YmdHis'))
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal PO')
                            ->default(now())
                            ->required(),
                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Status PO')
                            ->options([
                                'draft' => 'Draft',
                                'process' => 'Proses',
                                'completed' => 'Completed',
                                'cancelled' => 'Batal',
                            ])
                            ->default('draft')
                            ->required(),
                        Forms\Components\Textarea::make('deskripsi_order')
                            ->label('Deskripsi Order')
                            ->columnSpanFull(),
                        Forms\Components\DatePicker::make('deadline')
                            ->label('Deadline'),
                        Forms\Components\TextInput::make('total_amount')
                            ->label('Total (Otomatis dari Item)')
                            ->numeric()
                            ->readOnly()
                            ->prefix('Rp')
                            ->default(0),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Item PO')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Produk')
                                    ->options(fn (): array => \App\Models\Product::where('type', 'produk')->orderBy('name')->pluck('name', 'id')->all())
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Forms\Components\TextInput::make('qty')
                                    ->label('Qty')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, $get, Forms\Set $set) {
                                        $set('subtotal', ((float) ($state ?? 0)) * ((float) ($get('price') ?? 0)));
                                    }),
                                Forms\Components\TextInput::make('price')
                                    ->label('Harga')
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, $get, Forms\Set $set) {
                                        $set('subtotal', ((float) ($get('qty') ?? 0)) * ((float) ($state ?? 0)));
                                    }),
                                Forms\Components\TextInput::make('subtotal')
                                    ->label('Total')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly(),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $total = collect($state)->sum(function ($row) {
                                    return (float) ($row['subtotal'] ?? 0);
                                });
                                $set('total_amount', $total);
                            }),
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
                    ->label('Tanggal PO')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status PO')
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'process',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Item')
                    ->counts('items')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->formatStateUsing(fn ($state): string => 'Rp ' . number_format((float) ($state ?? 0), 0, ',', '.'))
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'process' => 'Proses',
                        'completed' => 'Completed',
                        'cancelled' => 'Batal',
                    ]),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
