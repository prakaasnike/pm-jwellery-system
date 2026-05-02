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

    protected static ?string $navigationIcon = 'heroicon-o-cube';

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
                Group::make()->schema([
                    Section::make('Product Details')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Product Name')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(2),
                            Forms\Components\TextInput::make('stone_name')
                                ->label('Stone Name')
                                ->maxLength(255)
                                ->columnSpan(1),

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
                                ->dehydrated()
                                ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                                ->suffix('gm')
                                ->numeric(),

                            Forms\Components\Select::make('category_id')
                                ->label('Category')
                                ->native(false)
                                ->relationship('category', 'name')
                                ->options($categories)
                                ->searchable()
                                ->preload(),
                            Forms\Components\Select::make('unit_id')
                                ->label('Unit')
                                ->native(false)
                                ->relationship('unit', 'name')
                                ->preload(),
                            Forms\Components\Select::make('type_id')
                                ->label('Types')
                                ->native(false)
                                ->relationship('types', 'name')
                                ->multiple()
                                ->options($types)
                                ->searchable()
                                ->preload(),
                        ])->columns(3),
                ])->columnSpan(2),

                Group::make()->schema([
                    Section::make('Image')
                        ->schema([
                            Forms\Components\FileUpload::make('product_image')
                                ->label('Product Image')
                                ->image()
                                ->maxFiles(1)
                                ->preserveFilenames()
                                ->maxSize(512 * 512 * 2)
                                ->imagePreviewHeight('120')
                                ->hiddenLabel(),
                        ]),
                ])->columnSpan(1),
            ])
            ->columns(3);
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
