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
        $customersByMonth = Customer::query()
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->whereYear('created_at', now()->year)
            ->groupByRaw('MONTH(created_at)')
            ->orderByRaw('MONTH(created_at)')
            ->pluck('total', 'month');

        $ordersByMonth = Order::query()
            ->selectRaw('MONTH(received_date) as month, COUNT(*) as total')
            ->whereYear('received_date', now()->year)
            ->groupByRaw('MONTH(received_date)')
            ->orderByRaw('MONTH(received_date)')
            ->pluck('total', 'month');

        $customerChart = array_map(fn ($m) => $customersByMonth->get($m, 0), range(1, 12));
        $orderChart    = array_map(fn ($m) => $ordersByMonth->get($m, 0), range(1, 12));

        return [
            Stat::make('Customers', Customer::count())
                ->description('Total customers joined')
                ->descriptionIcon('heroicon-m-user-group', IconPosition::Before)
                ->chart($customerChart)
                ->color('success'),

            Stat::make('Orders', Order::count())
                ->description('Total orders received')
                ->descriptionIcon('heroicon-m-shopping-bag', IconPosition::Before)
                ->chart($orderChart)
                ->color('warning'),

            Stat::make('Products', Product::count())
                ->description('Total products in stock')
                ->descriptionIcon('heroicon-m-rectangle-stack', IconPosition::Before)
                ->color('info'),
        ];
    }
}
