<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ChartOverview extends ApexChartWidget
{
    protected static ?string $chartId = 'chartOverview';
    protected static string $view = 'widgets.chart-overview';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $contentHeight = 300;

    public string $period = '1Y';
    public int $year = 0;

    public function mount(): void
    {
        $this->year = now()->year;
        parent::mount();
    }

    protected function getHeading(): ?string
    {
        return match ($this->period) {
            '1D'  => "Today's Orders — " . now()->format('d M Y'),
            '7D'  => 'Orders — Last 7 Days',
            '30D' => 'Orders — Last 30 Days',
            '1Y'  => 'Orders — ' . ($this->year ?: now()->year),
        };
    }

    public function setPeriod(string $p): void
    {
        $this->period = $p;
        $this->updateOptions();
    }

    public function updatedYear(): void
    {
        $this->updateOptions();
    }

    protected function getOptions(): array
    {
        [$categories, $received, $delivered, $columnWidth] = match ($this->period) {
            '1D'  => $this->buildHourly(),
            '7D'  => $this->buildDaily(7),
            '30D' => $this->buildDaily(30),
            '1Y'  => $this->buildMonthly(),
        };

        return [
            'chart' => [
                'type'    => 'bar',
                'height'  => 270,
                'toolbar' => ['show' => false],
                'animations' => ['enabled' => true, 'speed' => 400],
            ],
            'series' => [
                ['name' => 'Received', 'data' => $received, 'type' => 'column'],
                ['name' => 'Delivered', 'data' => $delivered, 'type' => 'line'],
            ],
            'stroke' => ['width' => [0, 3], 'curve' => 'smooth'],
            'dataLabels' => ['enabled' => false],
            'xaxis' => [
                'categories' => $categories,
                'labels' => [
                    'style'  => ['colors' => '#9ca3af', 'fontWeight' => 600],
                    'rotate' => $this->period === '30D' ? -45 : 0,
                ],
                'axisBorder' => ['show' => false],
                'axisTicks'  => ['show' => false],
            ],
            'yaxis' => [
                'min'            => 0,
                'forceNiceScale' => true,
                'labels'         => ['style' => ['colors' => '#9ca3af', 'fontWeight' => 600]],
            ],
            'grid'   => ['borderColor' => '#374151', 'strokeDashArray' => 4],
            'legend' => ['labels' => ['colors' => '#9ca3af', 'fontWeight' => 600]],
            'colors' => ['#c2732d', '#f0a059'],
            'fill'   => [
                'type'     => ['gradient', 'solid'],
                'gradient' => [
                    'shade'            => 'dark',
                    'type'             => 'vertical',
                    'shadeIntensity'   => 0.5,
                    'gradientToColors' => ['#d97706'],
                    'inverseColors'    => true,
                    'opacityFrom'      => 1,
                    'opacityTo'        => 0.7,
                    'stops'            => [0, 100],
                ],
            ],
            'plotOptions' => ['bar' => ['borderRadius' => 4, 'columnWidth' => $columnWidth]],
            'tooltip'     => ['theme' => 'dark'],
        ];
    }

    private function buildHourly(): array
    {
        $receivedByHour = Order::query()
            ->selectRaw('HOUR(received_date) as hour, COUNT(*) as total')
            ->whereDate('received_date', today())
            ->groupByRaw('HOUR(received_date)')
            ->pluck('total', 'hour');

        $deliveredByHour = Order::query()
            ->selectRaw('HOUR(updated_at) as hour, COUNT(*) as total')
            ->whereDate('updated_at', today())
            ->where('status', 'delivered')
            ->groupByRaw('HOUR(updated_at)')
            ->pluck('total', 'hour');

        $categories = $received = $delivered = [];
        for ($h = 0; $h < 24; $h++) {
            $categories[] = sprintf('%02d:00', $h);
            $received[]   = (int) ($receivedByHour[$h] ?? 0);
            $delivered[]  = (int) ($deliveredByHour[$h] ?? 0);
        }

        return [$categories, $received, $delivered, '50%'];
    }

    private function buildDaily(int $days): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $receivedByDate = Order::query()
            ->selectRaw('DATE(received_date) as date, COUNT(*) as total')
            ->where('received_date', '>=', $start)
            ->groupByRaw('DATE(received_date)')
            ->pluck('total', 'date');

        $deliveredByDate = Order::query()
            ->selectRaw('DATE(updated_at) as date, COUNT(*) as total')
            ->where('updated_at', '>=', $start)
            ->where('status', 'delivered')
            ->groupByRaw('DATE(updated_at)')
            ->pluck('total', 'date');

        $categories = $received = $delivered = [];
        foreach (CarbonPeriod::create($start, now()) as $date) {
            $key          = $date->toDateString();
            $categories[] = $days <= 7 ? $date->format('D d') : $date->format('d M');
            $received[]   = (int) ($receivedByDate[$key] ?? 0);
            $delivered[]  = (int) ($deliveredByDate[$key] ?? 0);
        }

        return [$categories, $received, $delivered, $days <= 7 ? '40%' : '70%'];
    }

    private function buildMonthly(): array
    {
        $year = $this->year ?: now()->year;

        $receivedByMonth = Order::query()
            ->selectRaw('MONTH(received_date) as month, COUNT(*) as total')
            ->whereYear('received_date', $year)
            ->groupByRaw('MONTH(received_date)')
            ->pluck('total', 'month');

        $deliveredByMonth = Order::query()
            ->selectRaw('MONTH(updated_at) as month, COUNT(*) as total')
            ->whereYear('updated_at', $year)
            ->where('status', 'delivered')
            ->groupByRaw('MONTH(updated_at)')
            ->pluck('total', 'month');

        $categories = $received = $delivered = [];
        for ($m = 1; $m <= 12; $m++) {
            $categories[] = Carbon::create()->month($m)->format('M');
            $received[]   = (int) ($receivedByMonth[$m] ?? 0);
            $delivered[]  = (int) ($deliveredByMonth[$m] ?? 0);
        }

        return [$categories, $received, $delivered, '60%'];
    }
}
