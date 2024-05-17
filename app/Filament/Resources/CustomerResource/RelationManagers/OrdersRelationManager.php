<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
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
                Section::make('Create an order for your customers')
                    ->description('')
                    ->schema([
                        TextInput::make('order_name')
                            ->required()
                            ->maxLength(255),

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
                            ->multiple(),
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
