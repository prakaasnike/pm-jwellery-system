<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ChartOverview extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'chartOverview';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Orders Overview';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getFilter(): ?string
    {
        return now()->year;
    }

    /**
     * Filter Options
     *
     * @return array|null
     */

    protected function getFilters(): ?array
    {
        $years = range(now()->year, 2023);

        return $years;
    }

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        // Fetch the total orders for each month
        $ordersByMonth = Order::query()
            ->selectRaw('MONTH(received_date) as month, COUNT(*) as total_orders')
            ->groupByRaw('MONTH(received_date)')
            ->orderByRaw('MONTH(received_date)')
            ->get();

        // Generate an array of all months within the date range of orders
        $allMonths = $ordersByMonth->map(function ($item) {
            return (new \DateTime())->setDate(2000, $item->month, 1)->format('M');
        })->toArray();

        // Fill in missing months with zero total orders
        $totalOrders = $ordersByMonth->pluck('total_orders')->toArray();

        return [
            'chart' => [
                'type' => 'line',
                'height' => 250,
                'toolbar' => [
                    'show' => false,
                ],
            ],
            'series' => [
                [
                    'name' => 'Orders',
                    'data' => $totalOrders,
                    'type' => 'column',
                ],
                [
                    'name' => 'Line',
                    'data' => $totalOrders,
                    'type' => 'line',
                ],
            ],
            'stroke' => [
                'width' => [0, 4],
                'curve' => 'smooth',
            ],
            'xaxis' => [
                'categories' => $allMonths,
                'labels' => [
                    'style' => [
                        'colors' => '#9ca3af',
                        'fontWeight' => 600,
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'colors' => '#9ca3af',
                        'fontWeight' => 600,
                    ],
                ],
            ],
            'legend' => [
                'labels' => [
                    'colors' => '#9ca3af',
                    'fontWeight' => 600,
                ],
            ],
            'colors' => ['#c2732d', '#f0a059'],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'shade' => 'dark',
                    'type' => 'vertical',
                    'shadeIntensity' => 0.5,
                    'gradientToColors' => ['#d97706'],
                    'inverseColors' => true,
                    'opacityFrom' => 1,
                    'opacityTo' => 1,
                    'stops' => [0, 100],
                ],
            ],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 10,
                ],
            ],
        ];
    }
}
