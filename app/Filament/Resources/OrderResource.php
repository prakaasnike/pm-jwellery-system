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
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class OrderResource extends Resource
{
    protected static ?int $navigationSort = 2;
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        $customers = Customer::all()->pluck('full_name', 'id');
        $products = Product::all()->pluck('name', 'id');

        return $form
            ->schema([
                Section::make('Create an order for your customers')
                    ->description('')
                    ->schema([
                        TextInput::make('order_name')
                            ->label('Order')
                            ->required()
                            ->maxLength(255),
                        Select::make('customer_id')
                            ->relationship('customer', 'full_name')
                            ->options($customers)
                            ->searchable()

                            ->required(),

                        ToggleButtons::make('status')
                            ->inline()
                            ->required()
                            ->options([
                                'received' => 'received',
                                'urgent' => 'urgent',
                                'ongoing' => 'ongoing',
                                'delivered' => 'delivered',
                            ]),
                        ToggleButtons::make('payment_status')
                            ->label('Payment Status')
                            ->inline()
                            ->required()
                            ->options([
                                'paid' => 'paid',
                                'unpaid' => 'unpaid',
                                'initialpaid' => 'initialpaid',
                            ]),
                        DatePicker::make('received_date')
                            ->required()
                            ->displayFormat('d/m/Y')
                            ->closeOnDateSelection()
                            ->weekStartsOnSunday()
                            ->native(false),
                        DatePicker::make('delivery_date')
                            ->required()
                            ->displayFormat('d/m/Y')
                            ->closeOnDateSelection()
                            ->weekStartsOnSunday()
                            ->native(false),
                        Select::make('product_id')
                            ->relationship('products', 'name')
                            ->options($products)
                            ->searchable()
                            ->multiple()
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(2)
                    ->columns(2),
                Group::make()->schema([
                    Section::make('Image')
                        ->schema([
                            FileUpload::make('order_image')
                                ->image()
                                ->maxFiles(4)
                                ->multiple()
                                ->preserveFilenames()
                                ->imagePreviewHeight('40')
                                ->maxSize(512 * 512 * 2),
                        ]), // Section title should not be standalone
                ])
                    ->columnSpan(1),
            ])

            ->columns([
                'default' => 3,
                'sm' => 3,
                'md' => 3,
                'lg' => 3,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.full_name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.phone')
                    ->label('Phone')
                    ->badge()
                    ->color('cyan'),
                Tables\Columns\TextColumn::make('order_name')
                    ->label('Order')
                    ->searchable(),
                Tables\Columns\TextColumn::make('products.name')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('order_image')
                    ->label('Order Image')
                    ->circular()
                    ->stacked()
                    ->defaultImageUrl(function ($record) {
                        // Generate random name for the avatar
                        $name = $record->order_name ?: 'Unknown';
                        // Construct the URL with the random name
                        return 'https://ui-avatars.com/api/?background=d97706&color=fff&name=' . urlencode($name);
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->sortable()
                    ->alignEnd()
                    // ->options(self::$statuses)
                    ->badge()
                    ->alignCenter()
                    ->color(fn (string $state): string => match ($state) {
                        'delivered' => 'success',
                        'ongoing' => 'warning',
                        'urgent' => 'danger',
                        'received' => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payment_status')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'unpaid' => 'warning',
                        'initialpaid' => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('received_date')
                    ->label('Order Date')
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
                    ExportBulkAction::make(),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
