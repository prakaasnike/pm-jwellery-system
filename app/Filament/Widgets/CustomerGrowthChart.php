<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use Carbon\Carbon;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class CustomerGrowthChart extends ApexChartWidget
{
    protected static ?string $chartId = 'customerGrowthChart';

    protected int|string|array $columnSpan = 1;

    protected static ?int $contentHeight = 300;

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    protected function getHeading(): ?string
    {
        return 'New Customers — '.now()->year;
    }

    protected function getOptions(): array
    {
        $byMonth = Customer::query()
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->whereYear('created_at', now()->year)
            ->groupByRaw('MONTH(created_at)')
            ->pluck('total', 'month');

        $categories = [];
        $data = [];
        for ($m = 1; $m <= 12; $m++) {
            $categories[] = Carbon::create()->month($m)->format('M');
            $data[] = (int) ($byMonth[$m] ?? 0);
        }

        return [
            'chart' => [
                'type' => 'area',
                'height' => 270,
                'toolbar' => ['show' => false],
                'animations' => ['enabled' => true, 'speed' => 400],
            ],
            'series' => [
                ['name' => 'Customers', 'data' => $data],
            ],
            'stroke' => ['width' => 2, 'curve' => 'smooth'],
            'dataLabels' => ['enabled' => false],
            'xaxis' => [
                'categories' => $categories,
                'labels' => ['style' => ['colors' => '#9ca3af', 'fontWeight' => 600]],
                'axisBorder' => ['show' => false],
                'axisTicks' => ['show' => false],
            ],
            'yaxis' => [
                'min' => 0,
                'forceNiceScale' => true,
                'labels' => ['style' => ['colors' => '#9ca3af', 'fontWeight' => 600]],
            ],
            'grid' => ['borderColor' => '#374151', 'strokeDashArray' => 4],
            'legend' => ['labels' => ['colors' => '#9ca3af']],
            'colors' => ['#6366f1'],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'shade' => 'dark',
                    'type' => 'vertical',
                    'shadeIntensity' => 0.4,
                    'opacityFrom' => 0.7,
                    'opacityTo' => 0.1,
                    'stops' => [0, 100],
                ],
            ],
            'tooltip' => ['theme' => 'dark'],
        ];
    }
}
