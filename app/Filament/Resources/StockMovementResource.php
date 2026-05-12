<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockMovementResource\Pages;
use App\Filament\Resources\StockMovementResource\RelationManagers;
use App\Models\StockMovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationLabel = 'Kartu Stok / Pergerakan';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('product_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('type')
                    ->required(),
                Forms\Components\TextInput::make('qty')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('reference'),
                Forms\Components\DatePicker::make('date')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'success' => 'in',
                        'danger' => 'out',
                    ]),
                Tables\Columns\TextColumn::make('qty')
                    ->formatStateUsing(fn ($state): string => number_format((float) ($state ?? 0), 0, ',', '.'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('reference')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->relationship('product', 'name'),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'in' => 'Masuk',
                        'out' => 'Keluar',
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
            'index' => Pages\ListStockMovements::route('/'),
            'create' => Pages\CreateStockMovement::route('/create'),
            'edit' => Pages\EditStockMovement::route('/{record}/edit'),
        ];
    }
}
