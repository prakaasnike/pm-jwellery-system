<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class PaymentStatusChart extends ApexChartWidget
{
    protected static ?string $chartId = 'paymentStatusChart';
    protected static ?string $heading = 'Payment Status';
    protected int | string | array $columnSpan = 1;
    protected static ?int $contentHeight = 300;

    protected function getOptions(): array
    {
        $statuses = Order::query()
            ->selectRaw('payment_status, COUNT(*) as total')
            ->groupBy('payment_status')
            ->pluck('total', 'payment_status');

        $labels = ['Paid', 'Unpaid', 'Initial Paid'];
        $keys   = ['paid', 'unpaid', 'initialpaid'];
        $data   = array_map(fn ($k) => (int) ($statuses[$k] ?? 0), $keys);

        return [
            'chart' => [
                'type'    => 'radialBar',
                'height'  => 300,
                'toolbar' => ['show' => false],
            ],
            'series' => array_map(
                fn ($val) => array_sum($data) > 0
                    ? round(($val / array_sum($data)) * 100, 1)
                    : 0,
                $data
            ),
            'labels' => $labels,
            'colors' => ['#10b981', '#ef4444', '#f97316'],
            'legend' => [
                'show'     => true,
                'position' => 'bottom',
                'labels'   => ['colors' => '#9ca3af'],
            ],
            'plotOptions' => [
                'radialBar' => [
                    'offsetY'     => -10,
                    'startAngle'  => -135,
                    'endAngle'    => 225,
                    'hollow'      => [
                        'margin' => 5,
                        'size'   => '30%',
                    ],
                    'track' => [
                        'background' => '#374151',
                        'strokeWidth' => '97%',
                        'margin'      => 5,
                    ],
                    'dataLabels' => [
                        'name'  => ['fontSize' => '13px', 'color' => '#9ca3af', 'offsetY' => -10],
                        'value' => ['fontSize' => '18px', 'color' => '#f9fafb', 'offsetY' => 4],
                        'total' => [
                            'show'  => true,
                            'label' => 'Orders',
                            'color' => '#9ca3af',
                            'formatter' => 'function (w) { return ' . array_sum($data) . ' }',
                        ],
                    ],
                ],
            ],
            'stroke' => ['lineCap' => 'round'],
        ];
    }
}
