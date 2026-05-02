<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

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
            'all' => Tab::make('All')
                ->badge($this->getStatusCount()),
            'received' => $this->statusTab('received'),
            'urgent' => $this->statusTab('urgent'),
            'ongoing' => $this->statusTab('ongoing'),
            'delivered' => $this->statusTab('delivered'),
        ];
    }

    private function statusTab(string $status): Tab
    {
        return Tab::make(OrderResource::getStatusOptions()[$status])
            ->badge($this->getStatusCount($status))
            ->badgeColor(OrderResource::getStatusColor($status))
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', $status));
    }

    private function getStatusCount(?string $status = null): int
    {
        return OrderResource::getEloquentQuery()
            ->when($status, fn (Builder $query): Builder => $query->where('status', $status))
            ->count();
    }
}
