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
        $years = range(now()->year, 2024);

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
        $currentYear = $this->filter ?? now()->year;

        // Fetch the total orders for each month based on received_date
        $ordersByReceivedMonth = Order::query()
            ->selectRaw('MONTH(received_date) as month, COUNT(*) as total_orders_received')
            ->whereYear('received_date', $currentYear)
            ->groupByRaw('MONTH(received_date)')
            ->orderByRaw('MONTH(received_date)')
            ->get()
            ->keyBy('month');

        // Fetch the total orders for each month based on updated_at
        $ordersByUpdatedMonth = Order::query()
            ->selectRaw('MONTH(updated_at) as month, COUNT(*) as total_orders_updated')
            ->whereYear('updated_at', $currentYear)
            ->groupByRaw('MONTH(updated_at)')
            ->orderByRaw('MONTH(updated_at)')
            ->get()
            ->keyBy('month');

        // Initialize arrays for all months
        $allMonths = [];
        $totalOrders = [];

        // Calculate the combined total orders for each month
        for ($month = 1; $month <= 12; $month++) {
            $allMonths[] = \DateTime::createFromFormat('!m', $month)->format('M');

            // Get received and updated orders count for the month
            $receivedOrders = $ordersByReceivedMonth->get($month)->total_orders_received ?? 0;
            $updatedOrders = $ordersByUpdatedMonth->get($month)->total_orders_updated ?? 0;

            // Subtract updated orders from received orders to avoid double counting
            $totalOrders[] = $receivedOrders - ($ordersByUpdatedMonth->get($month)->total_orders_updated ?? 0) + $updatedOrders;
        }
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
