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
    protected static ?string $heading = 'Monthly Orders Overview';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $contentHeight = 300;

    protected function getFilter(): ?string
    {
        return (string) now()->year;
    }

    protected function getFilters(): ?array
    {
        $years = range(now()->year, 2024);

        return array_combine($years, $years);
    }

    protected function getOptions(): array
    {
        $currentYear = (int) ($this->filter ?? now()->year);

        $receivedByMonth = Order::query()
            ->selectRaw('MONTH(received_date) as month, COUNT(*) as total')
            ->whereYear('received_date', $currentYear)
            ->groupByRaw('MONTH(received_date)')
            ->get()
            ->keyBy('month');

        $deliveredByMonth = Order::query()
            ->selectRaw('MONTH(updated_at) as month, COUNT(*) as total')
            ->whereYear('updated_at', $currentYear)
            ->where('status', 'delivered')
            ->groupByRaw('MONTH(updated_at)')
            ->get()
            ->keyBy('month');

        $months = [];
        $received = [];
        $delivered = [];

        for ($month = 1; $month <= 12; $month++) {
            $months[]    = \DateTime::createFromFormat('!m', $month)->format('M');
            $received[]  = $receivedByMonth->get($month)->total ?? 0;
            $delivered[] = $deliveredByMonth->get($month)->total ?? 0;
        }
        return [
            'chart' => [
                'type' => 'bar',
                'height' => 250,
                'toolbar' => [
                    'show' => false,
                ],
            ],
            'series' => [
                [
                    'name' => 'Received',
                    'data' => $received,
                    'type' => 'column',
                ],
                [
                    'name' => 'Delivered',
                    'data' => $delivered,
                    'type' => 'line',
                ],
            ],
            'stroke' => [
                'width' => [0, 3],
                'curve' => 'smooth',
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'xaxis' => [
                'categories' => $months,
                'labels' => [
                    'style' => [
                        'colors' => '#9ca3af',
                        'fontWeight' => 600,
                    ],
                ],
            ],
            'yaxis' => [
                'min' => 0,
                'forceNiceScale' => true,
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
                'type' => ['gradient', 'solid'],
                'gradient' => [
                    'shade' => 'dark',
                    'type' => 'vertical',
                    'shadeIntensity' => 0.5,
                    'gradientToColors' => ['#d97706'],
                    'inverseColors' => true,
                    'opacityFrom' => 1,
                    'opacityTo' => 0.8,
                    'stops' => [0, 100],
                ],
            ],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 6,
                    'columnWidth' => '60%',
                ],
            ],
        ];
    }
}
