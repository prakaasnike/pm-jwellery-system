<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Filament\Tables\Table;
use Filament\Tables\HasTableColumns;
use App\Models\OrderStatus;
use Filament\Forms\Components\Tabs;
// use Filament\Tables\Tab; // Import Tab class

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'All' => Tab::make('All'),
            'received' => Tab::make('Received')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'received');
                }),
            'urgent' => Tab::make('Urgent')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'urgent');
                }),
            'ongoing' => Tab::make('Ongoing')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'ongoing');
                }),
            'delivered' => Tab::make('Delivered')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'delivered');
                }),


        ]; // Add a semicolon here
    }
}
