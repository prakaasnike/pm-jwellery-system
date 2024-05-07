<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Customer', Customer::count())
                ->description('Customers that have been joined recently')
                ->descriptionIcon('heroicon-m-user-group', IconPosition::Before)
                ->chart([4, 20, 5, 60, 30, 90, 30])
                ->color('success'),

            Stat::make('Order', Order::count())
                ->description('Orders that have been created recently')
                ->descriptionIcon('heroicon-m-shopping-bag', IconPosition::Before)
                ->chart([4, 20, 5, 60, 30, 90, 30])
                ->color('danger'),

            Stat::make('Product', Product::count())
                ->description('Product that have been created recently')
                ->descriptionIcon('heroicon-m-rectangle-stack', IconPosition::Before)
                ->chart([4, 20, 5, 60, 30, 90, 30])
                ->color('info')
        ];
    }
}
