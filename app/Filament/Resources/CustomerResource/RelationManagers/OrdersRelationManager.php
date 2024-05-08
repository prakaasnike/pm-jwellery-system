<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    public function form(Form $form): Form
    {
        $products = Product::all()->pluck('name', 'id');
        return $form
            ->schema([
                Forms\Components\TextInput::make('order_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('order_image')
                    ->image()
                    ->directory('order-images')
                    ->maxFiles(4)
                    ->multiple()
                    ->preserveFilenames()
                    ->imagePreviewHeight('40')
                    ->maxSize(512 * 512 * 2),

                Forms\Components\Select::make('product')
                    ->relationship('products', 'name')
                    ->options($products)
                    ->searchable()
                    ->multiple(),
                Forms\Components\Select::make('status_id')
                    ->relationship('status', 'name'),
                Forms\Components\Select::make('payment_id')
                    ->relationship('payment', 'name'),
                Forms\Components\DatePicker::make('received_date')
                    ->required()
                    ->displayFormat('d/m/Y')
                    ->closeOnDateSelection()
                    ->weekStartsOnSunday()
                    ->native(false),
                Forms\Components\DatePicker::make('delivery_date')
                    ->required()
                    ->displayFormat('d/m/Y')
                    ->weekStartsOnSunday()
                    ->closeOnDateSelection()
                    ->native(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('order_name')
            ->columns([
                Tables\Columns\TextColumn::make('order_name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
