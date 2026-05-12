<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Master Produk';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('type', 'produk');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Master Produk')
                    ->schema([
                        Forms\Components\Hidden::make('type')
                            ->default('produk'),
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Produk')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('sku')
                            ->label('Kode Produk')
                            ->unique(ignoreRecord: true)
                            ->required(),
                        Forms\Components\Select::make('unit')
                            ->label('Satuan')
                            ->options(fn (): array => Unit::query()->orderBy('name')->pluck('name', 'name')->all())
                            ->searchable()
                            ->required()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Satuan')
                                    ->required()
                                    ->maxLength(50),
                            ])
                            ->createOptionUsing(function (array $data): string {
                                $unit = Unit::firstOrCreate(['name' => $data['name']]);
                                return (string) $unit->name;
                            }),
                        Forms\Components\Textarea::make('description')
                            ->label('Keterangan')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sku')
                    ->label('Kode Produk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit')
                    ->label('Satuan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Keterangan')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(50),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
