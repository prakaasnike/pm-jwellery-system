<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('product_image')
                    ->image()
                    ->maxFiles(1)
                    ->preserveFilenames()
                    ->maxSize(512 * 512 * 2)
                    ->imagePreviewHeight('90')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('stone_weight')
                    ->required()
                    ->numeric()
                    ->inputMode('float')
                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                    ->afterStateUpdated(function (callable $get, callable $set) {
                        $totalWeight = (float) $get('product_total_weight') ?? 0;
                        $stoneWeight = (float) $get('stone_weight') ?? 0;
                        $netWeight = $totalWeight - $stoneWeight;
                        $set('product_net_weight', number_format($netWeight, 2));
                        // Update the model attribute
                        $set('model.product_net_weight', (float) $netWeight);

                    }),
                Forms\Components\TextInput::make('product_net_weight')
                    ->numeric()
                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                    ->live()
                    ->inputMode('float')
                    ->dehydrated(),
                Forms\Components\TextInput::make('product_total_weight')
                    ->required()
                    ->numeric()
                    ->inputMode('float')
                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                    ->afterStateUpdated(function (callable $get, callable $set) {
                        $totalWeight = (float) $get('product_total_weight') ?? 0;
                        $stoneWeight = (float) $get('stone_weight') ?? 0;
                        $netWeight = $totalWeight - $stoneWeight;
                        $set('product_net_weight', number_format($netWeight, 2));
                        // Update the model attribute
                        $set('model.product_net_weight', (float) $netWeight);

                    }),
                Forms\Components\Select::make('unit_id')
                    ->relationship('unit', 'name'),
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name'),
                Forms\Components\Select::make('type')
                    ->relationship('types', 'name')
                    ->multiple(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('product_image')
                    ->label('Thumbnail')
                    ->square()
                    ->stacked(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Product Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stone_weight')
                    ->label('Stone Weight')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product_net_weight')
                    ->label('Net Weight')
                    ->numeric()
                    ->sortable()
                    ->getStateUsing(function (Product $record) {
                        return $record->product_total_weight - $record->stone_weight;
                    }),

                Tables\Columns\TextColumn::make('product_total_weight')
                    ->label('Total Weight')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('types.name')
                    ->badge(),
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
        return [
            RelationManagers\TypesRelationManager::class,
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
