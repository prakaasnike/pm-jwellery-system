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

            'all' => Tab::make('All Orders'),
            'received Order' => Tab::make('Received Order')
                ->modifyQueryUsing(function ($query) {
                    return $query->whereHas('status', function ($query) {
                        $query->where('name', 'Received');
                    });
                }),
            'urgent Order' => Tab::make('Urgent Orders')
                ->modifyQueryUsing(function ($query) {
                    return $query->whereHas('status', function ($query) {
                        $query->where('name', 'Urgent');
                    });
                }),
            'ongoing Order' => Tab::make('Ongoing Orders')
                ->modifyQueryUsing(function ($query) {
                    return $query->whereHas('status', function ($query) {
                        $query->where('name', 'Ongoing');
                    });
                }),
            'delivered Order' => Tab::make('Delivered Orders')
                ->modifyQueryUsing(function ($query) {
                    return $query->whereHas('status', function ($query) {
                        $query->where('name', 'Delivered');
                    });
                }),

        ]; // Add a semicolon here
    }
}
