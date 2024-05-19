<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Category;
use App\Models\Product;
use App\Models\Type;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?int $navigationSort = 3;
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $updateNetWeight = function (callable $get, callable $set) {
            $totalWeight = ($get('product_total_weight') ?? 0);
            $stoneWeight = ($get('stone_weight') ?? 0);
            $netWeight = $totalWeight - $stoneWeight;
            $set('product_net_weight', number_format($netWeight, 2));
            // Update the model attribute
            $set('model.product_net_weight', $netWeight);
        };

        $categories = Category::all()->pluck('name', 'id');
        $types = Type::all()->pluck('name', 'id')->toArray();

        return $form
            ->schema([
                Section::make('Enter your product details')
                    ->description('')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Product Name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('stone_name')
                            ->label('Stone Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('stone_weight')
                                    ->label('Stone Weight')
                                    ->required()
                                    ->live()
                                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                                    ->numeric()
                                    ->suffix('gm')
                                    ->afterStateUpdated($updateNetWeight),
                                Forms\Components\TextInput::make('product_total_weight')
                                    ->label('Total Weight')
                                    ->required()
                                    ->numeric()
                                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                                    ->live()
                                    ->suffix('gm')
                                    ->afterStateUpdated($updateNetWeight),
                                Forms\Components\TextInput::make('product_net_weight')
                                    ->label('Net Weight')
                                    ->disabled()
                                    ->live(debounce: 500)
                                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                                    ->dehydrated()
                                    ->suffix('gm')
                                    ->numeric(),
                            ]),

                        Forms\Components\Select::make('unit_id')
                            ->native(false)
                            ->relationship('unit', 'name'),
                        Forms\Components\Select::make('category_id')
                            ->native(false)
                            ->relationship('category', 'name')
                            ->options($categories)
                            ->searchable(),
                    ])->columnSpan(2)->columns(2),
                // Group Column
                Group::make()->schema([
                    Section::make("Image")
                        ->collapsible()
                        ->schema([
                            Forms\Components\FileUpload::make('product_image')
                                ->required()
                                ->image()
                                ->maxFiles(1)
                                ->preserveFilenames()
                                ->maxSize(512 * 512 * 2)
                                ->imagePreviewHeight('90'),
                        ]),
                    Section::make("Type")
                        ->schema([
                            Forms\Components\Select::make('type_id')
                                ->native(false)
                                ->relationship('types', 'name')
                                ->multiple()
                                ->options($types)
                                ->searchable(),
                        ])
                        ->columnSpan(1),
                ]),
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
                Tables\Columns\ImageColumn::make('product_image')
                    ->label('Image')
                    ->square(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Product')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('stone_name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('stone_weight')
                    ->label('Stone Wt.')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->suffix(' gm')
                    ->searchable(),
                Tables\Columns\TextColumn::make('product_net_weight')
                    ->label('Net Wt.')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->suffix(' gm'),

                Tables\Columns\TextColumn::make('product_total_weight')
                    ->label('Total Wt.')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->searchable()
                    ->suffix(' gm'),
                Tables\Columns\TextColumn::make('unit.name')
                    ->default('gm')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('types.name')
                    ->badge()
                    ->searchable(),
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
        return [];
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
