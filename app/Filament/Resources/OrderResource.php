<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\OrderResource\RelationManagers\CustomersRelationManager;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Status;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $customers = Customer::all()->pluck('full_name', 'id');
        $products = Product::all()->pluck('name', 'id');

        return $form
            ->schema([
                Forms\Components\TextInput::make('order_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('order_image')
                    ->image()
                    ->directory('order_image')
                    ->maxFiles(4)
                    ->multiple()
                    ->preserveFilenames()
                    ->imagePreviewHeight('40')
                    ->maxSize(512 * 512 * 2),
                Forms\Components\Select::make('customer_id')
                    ->relationship('customer', 'full_name')
                    ->options($customers)
                    ->searchable(),

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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.full_name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('products.name')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('order_image')
                    ->circular()
                    ->stacked()
                    ->defaultImageUrl(function ($record) {
                        // Generate random name for the avatar
                        $name = $record->order_name ?: 'Unknown';
                        // Construct the URL with the random name
                        return 'https://ui-avatars.com/api/?background=d97706&color=fff&name=' . urlencode($name);
                    }),
                Tables\Columns\TextColumn::make('status.name')
                    ->sortable()
                    ->alignEnd()
                    // ->options(self::$statuses)
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Delivered' => 'success',
                        'Ongoing' => 'warning',
                        'Urgent' => 'danger',
                        'Received' => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payment.name')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Paid' => 'success',
                        'Unpaid' => 'warning',
                        'Initial Payment' => 'gray',
                    }),
                Tables\Columns\TextColumn::make('received_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('delivery_date')
                    ->badge()
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])

            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                ])
            ])
            ->defaultSort('delivery_date', 'asc')
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
