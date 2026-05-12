<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PackingResource\Pages;
use App\Models\Packing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PackingResource extends Resource
{
    protected static ?string $model = Packing::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Packing';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        $recalc = function ($get, Forms\Set $set) {
            $totalPcs = (float) ($get('total_pcs') ?? 0);
            $isiPerPack = (float) ($get('isi_per_pack') ?? 0);

            if ($isiPerPack <= 0) {
                $set('total_pack', 0);
                $set('sisa_pcs', $totalPcs);
                return;
            }

            $totalPack = (int) floor($totalPcs / $isiPerPack);
            $sisa = $totalPcs - ($totalPack * $isiPerPack);

            $set('total_pack', $totalPack);
            $set('sisa_pcs', $sisa);
        };

        return $form->schema([
            Forms\Components\Section::make('Packing')
                ->schema([
                    Forms\Components\Placeholder::make('po_info')
                        ->label('PO')
                        ->content(fn (?Packing $record): string => $record?->production?->purchaseOrder?->number ?? '-'),
                    Forms\Components\Placeholder::make('item_info')
                        ->label('Item / Produk')
                        ->content(fn (?Packing $record): string => $record?->production?->purchaseOrderItem?->product?->name ?? '-'),
                    Forms\Components\Select::make('production_id')
                        ->label('Produksi')
                        ->relationship('production', 'number', modifyQueryUsing: fn ($query) => $query->where('status', 'completed')->orderBy('number'))
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\DatePicker::make('tanggal_packing')
                        ->default(now())
                        ->required(),
                    Forms\Components\Select::make('status')
                        ->label('Status Packing')
                        ->options([
                            'belum_mulai' => 'Belum Packing',
                            'proses' => 'Proses',
                            'complete' => 'Sudah Packing',
                        ])
                        ->required()
                        ->default('belum_mulai'),
                    Forms\Components\TextInput::make('total_pcs')
                        ->label('Total Qty Produk')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required()
                        ->readOnly(),
                    Forms\Components\TextInput::make('isi_per_pack')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, $get, Forms\Set $set) => $recalc($get, $set)),
                    Forms\Components\TextInput::make('total_pack')
                        ->numeric()
                        ->readOnly(),
                    Forms\Components\TextInput::make('sisa_pcs')
                        ->numeric()
                        ->readOnly(),
                    Forms\Components\Textarea::make('catatan')
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('production.purchaseOrder.number')
                    ->label('PO')
                    ->searchable(),
                Tables\Columns\TextColumn::make('production.purchaseOrderItem.product.name')
                    ->label('Item / Produk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_packing')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_pcs')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => 'belum_mulai',
                        'warning' => 'proses',
                        'success' => 'complete',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'belum_mulai' => 'Belum Packing',
                        'proses' => 'Proses',
                        'complete' => 'Sudah Packing',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'belum_mulai' => 'Belum Packing',
                        'proses' => 'Proses',
                        'complete' => 'Sudah Packing',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('changeStatus')
                    ->label('Ubah Status')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Status Packing')
                            ->options([
                                'belum_mulai' => 'Belum Packing',
                                'proses' => 'Proses',
                                'complete' => 'Sudah Packing',
                            ])
                            ->required(),
                    ])
                    ->action(function (array $data, \App\Models\Packing $record): void {
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
            'index' => Pages\ListPackings::route('/'),
            'edit' => Pages\EditPacking::route('/{record}/edit'),
        ];
    }
}
