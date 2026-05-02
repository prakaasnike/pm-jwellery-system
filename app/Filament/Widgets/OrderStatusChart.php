<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class OrderStatusChart extends ApexChartWidget
{
    protected static ?string $chartId = 'orderStatusChart';
    protected static ?string $heading = 'Order Status';
    protected int | string | array $columnSpan = 1;
    protected static ?int $contentHeight = 300;

    protected function getOptions(): array
    {
        $statuses = Order::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $labels = ['Received', 'Urgent', 'Ongoing', 'Delivered'];
        $keys   = ['received', 'urgent', 'ongoing', 'delivered'];
        $data   = array_map(fn ($k) => (int) ($statuses[$k] ?? 0), $keys);

        return [
            'chart' => [
                'type'    => 'donut',
                'height'  => 300,
                'toolbar' => ['show' => false],
            ],
            'series' => $data,
            'labels' => $labels,
            'colors' => ['#3b82f6', '#ef4444', '#f59e0b', '#10b981'],
            'legend' => [
                'position' => 'bottom',
                'labels'   => ['colors' => '#9ca3af'],
            ],
            'dataLabels' => [
                'enabled' => true,
                'style'   => ['fontSize' => '13px', 'fontWeight' => 600],
            ],
            'plotOptions' => [
                'pie' => [
                    'donut' => [
                        'size'   => '65%',
                        'labels' => [
                            'show'  => true,
                            'total' => [
                                'show'  => true,
                                'label' => 'Total',
                                'color' => '#9ca3af',
                            ],
                        ],
                    ],
                ],
            ],
            'stroke' => ['width' => 0],
        ];
    }
}
