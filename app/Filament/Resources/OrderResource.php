<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
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
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class OrderResource extends Resource
{
    protected static ?int $navigationSort = 2;

    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function getStatusOptions(): array
    {
        return [
            'received' => 'Received',
            'urgent' => 'Urgent',
            'ongoing' => 'Ongoing',
            'delivered' => 'Delivered',
        ];
    }

    public static function getPaymentStatusOptions(): array
    {
        return [
            'paid' => 'Paid',
            'unpaid' => 'Unpaid',
            'initialpaid' => 'Initial paid',
        ];
    }

    public static function getStatusColors(): array
    {
        return [
            'received' => 'gray',
            'urgent' => 'danger',
            'ongoing' => 'warning',
            'delivered' => 'success',
        ];
    }

    public static function getPaymentStatusColors(): array
    {
        return [
            'paid' => 'success',
            'unpaid' => 'warning',
            'initialpaid' => 'gray',
        ];
    }

    public static function getStatusColor(string $state): string
    {
        return self::getStatusColors()[$state] ?? 'gray';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (! $user || $user->hasRole('super_admin')) {
            return $query;
        }

        return $query->whereHas('customer', fn (Builder $query) => $query->where('user_id', $user->id));
    }

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $customerQuery = Customer::query();

        if ($user && ! $user->hasRole('super_admin')) {
            $customerQuery->where('user_id', $user->id);
        }

        $customers = $customerQuery->pluck('full_name', 'id');
        $products = Product::all()->pluck('name', 'id');

        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make('Order details')
                            ->schema([
                                Select::make('customer_id')
                                    ->label('Customer')
                                    ->relationship('customer', 'full_name')
                                    ->options($customers)
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn () => auth()->user()?->hasRole('super_admin') === false)
                                    ->required(),
                                TextInput::make('order_name')
                                    ->label('Order')
                                    ->required()
                                    ->maxLength(255),
                                Select::make('product_id')
                                    ->label('Products')
                                    ->relationship('products', 'name')
                                    ->options($products)
                                    ->searchable()
                                    ->multiple()
                                    ->preload()
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->compact(),
                        Section::make('Images')
                            ->compact()
                            ->collapsible()
                            ->schema([
                                FileUpload::make('order_image')
                                    ->label('Images')
                                    ->image()
                                    ->maxFiles(4)
                                    ->multiple()
                                    ->preserveFilenames()
                                    ->imagePreviewHeight('80')
                                    ->maxSize(512 * 512 * 2),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),
                Group::make()
                    ->schema([
                        Section::make('Status')
                            ->schema([
                                ToggleButtons::make('status')
                                    ->grouped()
                                    ->required()
                                    ->options(self::getStatusOptions())
                                    ->colors(self::getStatusColors()),
                                ToggleButtons::make('payment_status')
                                    ->label('Payment')
                                    ->grouped()
                                    ->required()
                                    ->options(self::getPaymentStatusOptions())
                                    ->colors(self::getPaymentStatusColors()),
                                DatePicker::make('received_date')
                                    ->label('Order date')
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
                            ])
                            ->compact(),
                    ])
                    ->columnSpan(['lg' => 1]),
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
                    ->label('Customer')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.phone')
                    ->label('Phone')
                    ->badge()
                    ->color('cyan'),
                Tables\Columns\TextColumn::make('order_name')
                    ->label('Order')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('status')
                    ->sortable()
                    ->badge()
                    ->alignCenter()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => self::getStatusColor($state))
                    ->tooltip(fn (Order $record): ?string => auth()->user()?->can('update', $record) ? 'Click to change status' : null)
                    ->action(
                        Tables\Actions\Action::make('changeStatus')
                            ->label('Change Status')
                            ->icon('heroicon-o-arrow-path')
                            ->modalHeading(fn (Order $record): string => "Change status: {$record->order_name}")
                            ->fillForm(fn (Order $record): array => [
                                'status' => $record->status,
                            ])
                            ->form([
                                Select::make('status')
                                    ->label('Status')
                                    ->required()
                                    ->options(self::getStatusOptions())
                                    ->native(false),
                            ])
                            ->action(fn (Order $record, array $data): bool => $record->update([
                                'status' => $data['status'],
                            ]))
                            ->visible(fn (Order $record): bool => auth()->user()?->can('update', $record) ?? false),
                    ),
                Tables\Columns\TextColumn::make('received_date')
                    ->label('Order Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('delivery_date')
                    ->badge()
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('products.name')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ImageColumn::make('order_image')
                    ->label('Order Image')
                    ->circular()
                    ->stacked()
                    ->defaultImageUrl(function ($record) {
                        // Generate random name for the avatar
                        $name = $record->order_name ?: 'Unknown';

                        // Construct the URL with the random name
                        return 'https://ui-avatars.com/api/?background=d97706&color=fff&name='.urlencode($name);
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn (string $state): string => self::getPaymentStatusColors()[$state] ?? 'gray')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->options(fn (): array => Customer::query()
                        ->orderBy('full_name')
                        ->pluck('full_name', 'id')
                        ->all())
                    ->searchable()
                    ->preload(),
                SelectFilter::make('payment_status')
                    ->label('Payment')
                    ->options(self::getPaymentStatusOptions()),
                Filter::make('due_today')
                    ->label('Due today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('delivery_date', today())),
                Filter::make('overdue')
                    ->label('Overdue')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereDate('delivery_date', '<', today())
                        ->where('status', '!=', 'delivered')),
                Filter::make('received_date')
                    ->form([
                        DatePicker::make('from')
                            ->label('Order from')
                            ->native(false),
                        DatePicker::make('until')
                            ->label('Order until')
                            ->native(false),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('received_date', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('received_date', '<=', $date))),
            ])
            ->filtersTriggerAction(fn (Tables\Actions\Action $action): Tables\Actions\Action => $action->button()->label('Filters'))
            ->persistFiltersInSession()
            ->persistSearchInSession()

            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('status')
                        ->label('Change status')
                        ->icon('heroicon-o-arrow-path')
                        ->modalHeading(fn (Order $record): string => "Change status: {$record->order_name}")
                        ->fillForm(fn (Order $record): array => [
                            'status' => $record->status,
                        ])
                        ->form([
                            Select::make('status')
                                ->label('Status')
                                ->required()
                                ->options(self::getStatusOptions())
                                ->native(false),
                        ])
                        ->action(fn (Order $record, array $data): bool => $record->update([
                            'status' => $data['status'],
                        ]))
                        ->visible(fn (Order $record): bool => auth()->user()?->can('update', $record) ?? false),
                    Tables\Actions\EditAction::make()
                        ->visible(fn (Order $record): bool => auth()->user()?->can('update', $record) ?? false),
                ]),
            ])
            ->recordUrl(null)
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
