<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class OrderStatusChart extends ApexChartWidget
{
    protected static ?string $chartId = 'orderStatusChart';

    protected static string $view = 'widgets.order-status-chart';

    protected int|string|array $columnSpan = 1;

    protected static ?int $contentHeight = 300;

    public string $period = '7D';

    public int $year = 0;

    private const STATUSES = [
        'received' => 'Received',
        'urgent' => 'Urgent',
        'ongoing' => 'Ongoing',
        'delivered' => 'Delivered',
    ];

    private const COLORS = [
        'received' => '#3b82f6',
        'urgent' => '#ef4444',
        'ongoing' => '#f59e0b',
        'delivered' => '#10b981',
    ];

    public function mount(): void
    {
        $this->year = now()->year;

        parent::mount();
    }

    protected function getHeading(): ?string
    {
        return match ($this->period) {
            '1D' => 'Orders by Status - Today',
            '7D' => 'Orders by Status - Last 7 Days',
            '30D' => 'Orders by Status - Last 30 Days',
            '1Y' => 'Orders by Status - '.($this->year ?: now()->year),
            default => 'Orders by Status',
        };
    }

    public function setPeriod(string $period): void
    {
        if (! in_array($period, ['1D', '7D', '30D', '1Y'], true)) {
            return;
        }

        $this->period = $period;
        $this->updateOptions();
    }

    public function updatedYear(): void
    {
        $this->year = (int) $this->year;
        $this->updateOptions();
    }

    public function getAvailableYears(): array
    {
        $firstOrderYear = (int) (Order::query()
            ->selectRaw('MIN(YEAR(received_date)) as year')
            ->value('year') ?: now()->year);

        return range(now()->year, min($firstOrderYear, now()->year));
    }

    protected function getOptions(): array
    {
        return $this->period === '1Y'
            ? $this->getYearlyOptions()
            : $this->getStatusTotalOptions();
    }

    private function getStatusTotalOptions(): array
    {
        $statuses = $this->periodQuery(Order::query())
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $keys = array_keys(self::STATUSES);
        $data = array_map(fn (string $status): int => (int) ($statuses[$status] ?? 0), $keys);

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 270,
                'toolbar' => ['show' => false],
                'animations' => ['enabled' => true, 'speed' => 400],
            ],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 5,
                    'columnWidth' => '50%',
                    'distributed' => true,
                ],
            ],
            'series' => [
                ['name' => 'Orders', 'data' => $data],
            ],
            'xaxis' => [
                'categories' => array_values(self::STATUSES),
                'labels' => [
                    'style' => ['colors' => array_fill(0, 4, '#9ca3af'), 'fontWeight' => 700, 'fontSize' => '13px'],
                ],
                'axisBorder' => ['show' => false],
                'axisTicks' => ['show' => false],
            ],
            'yaxis' => [
                'min' => 0,
                'forceNiceScale' => true,
                'labels' => [
                    'style' => ['colors' => '#9ca3af', 'fontWeight' => 600],
                ],
            ],
            'colors' => array_values(self::COLORS),
            'dataLabels' => [
                'enabled' => true,
                'style' => ['fontSize' => '13px', 'fontWeight' => 700, 'colors' => ['#fff']],
            ],
            'grid' => ['borderColor' => '#374151', 'strokeDashArray' => 4],
            'legend' => ['show' => false],
            'tooltip' => ['theme' => 'dark'],
        ];
    }

    private function getYearlyOptions(): array
    {
        $year = $this->year ?: now()->year;

        $monthlyTotals = Order::query()
            ->selectRaw('MONTH(received_date) as month, status, COUNT(*) as total')
            ->whereYear('received_date', $year)
            ->groupByRaw('MONTH(received_date), status')
            ->get()
            ->groupBy('status');

        $series = [];

        foreach (self::STATUSES as $status => $label) {
            $totals = $monthlyTotals->get($status, collect())->pluck('total', 'month');

            $series[] = [
                'name' => $label,
                'data' => array_map(
                    fn (int $month): int => (int) ($totals[$month] ?? 0),
                    range(1, 12),
                ),
            ];
        }

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 270,
                'stacked' => true,
                'toolbar' => ['show' => false],
                'animations' => ['enabled' => true, 'speed' => 400],
            ],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 4,
                    'columnWidth' => '55%',
                ],
            ],
            'series' => $series,
            'xaxis' => [
                'categories' => array_map(
                    fn (int $month): string => Carbon::create()->month($month)->format('M'),
                    range(1, 12),
                ),
                'labels' => [
                    'style' => ['colors' => '#9ca3af', 'fontWeight' => 600],
                ],
                'axisBorder' => ['show' => false],
                'axisTicks' => ['show' => false],
            ],
            'yaxis' => [
                'min' => 0,
                'forceNiceScale' => true,
                'labels' => [
                    'style' => ['colors' => '#9ca3af', 'fontWeight' => 600],
                ],
            ],
            'colors' => array_values(self::COLORS),
            'dataLabels' => ['enabled' => false],
            'grid' => ['borderColor' => '#374151', 'strokeDashArray' => 4],
            'legend' => [
                'position' => 'top',
                'horizontalAlign' => 'left',
                'labels' => ['colors' => '#9ca3af'],
                'markers' => ['radius' => 4],
            ],
            'tooltip' => ['theme' => 'dark'],
        ];
    }

    private function periodQuery(Builder $query): Builder
    {
        return match ($this->period) {
            '1D' => $query->whereDate('received_date', today()),
            '7D' => $query->whereDate('received_date', '>=', now()->subDays(6)->toDateString()),
            '30D' => $query->whereDate('received_date', '>=', now()->subDays(29)->toDateString()),
            default => $query,
        };
    }

    protected function extraJsOptions(): ?RawJs
    {
        return RawJs::make(<<<'JS'
        {
            dataLabels: {
                formatter: function (val) {
                    return val > 0 ? val : ''
                }
            }
        }
        JS);
    }
}
