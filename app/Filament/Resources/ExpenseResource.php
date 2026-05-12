<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Filament\Resources\ExpenseResource\RelationManagers;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Pengeluaran Keuangan';
    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Pengeluaran')
                    ->schema([
                        Forms\Components\Select::make('expense_category_id')
                            ->relationship('category', 'name')
                            ->placeholder('Pilih kategori biaya')
                            ->required(),
                        Forms\Components\DatePicker::make('date')
                            ->default(now())
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->required()
                            ->prefix('Rp'),
                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Referensi (Opsional)')
                    ->schema([
                        Forms\Components\Select::make('reference_type')
                            ->options([
                                'App\Models\PurchaseOrder' => 'Purchase Order',
                                'App\Models\Production' => 'Produksi',
                            ])
                            ->placeholder('Pilih tipe')
                            ->live(),
                        Forms\Components\Select::make('reference_id')
                            ->label('Pilih Referensi')
                            ->options(function ($get) {
                                $type = $get('reference_type');
                                if ($type === 'App\Models\PurchaseOrder') {
                                    return \App\Models\PurchaseOrder::pluck('number', 'id');
                                }
                                if ($type === 'App\Models\Production') {
                                    return \App\Models\Production::pluck('number', 'id');
                                }
                                return [];
                            })
                            ->placeholder('Pilih referensi')
                            ->hidden(fn ($get) => !$get('reference_type')),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->formatStateUsing(fn ($state): string => 'Rp ' . number_format((float) ($state ?? 0), 0, ',', '.'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('reference_type')
                    ->label('Tipe Ref')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('reference.number')
                    ->label('Nomor Ref'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make(),
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
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
