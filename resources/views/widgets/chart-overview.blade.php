@php
    $chartId      = $this->getChartId();
    $chartOptions = $this->getOptions();
    $contentHeight = $this->getContentHeight();
    $pollingInterval = $this->getPollingInterval();
    $loadingIndicator = $this->getLoadingIndicator();
    $deferLoading = $this->getDeferLoading();
    $readyToLoad  = $this->readyToLoad;
    $darkMode     = $this->getDarkMode();
    $extraJsOptions = $this->extraJsOptions();
    $periods      = ['1D', '7D', '30D', '1Y'];
@endphp

<x-filament-widgets::widget class="filament-widgets-chart-widget filament-apex-charts-widget">
    <x-filament::card class="filament-apex-charts-card">
        <div x-data="{ dropdownOpen: false }">

            {{-- Custom header with period buttons --}}
            <div class="flex items-center justify-between gap-4 py-2">
                <div>
                    <div class="text-base font-semibold leading-6">
                        {{ $this->getHeading() }}
                    </div>
                </div>

                <div class="flex items-center gap-1">
                    @foreach ($periods as $p)
                        <button
                            wire:click="setPeriod('{{ $p }}')"
                            style="padding:2px 10px;font-size:11px;font-weight:600;border-radius:5px;cursor:pointer;
                                {{ $this->period === $p
                                    ? 'background:#c2732d;color:#fff;border:1px solid #c2732d;'
                                    : 'background:transparent;color:#9ca3af;border:1px solid #374151;' }}"
                        >{{ $p }}</button>
                    @endforeach

                    @if ($this->period === '1Y')
                        <select
                            wire:model.live="year"
                            style="margin-left:6px;font-size:12px;padding:1px 6px;border-radius:5px;border:1px solid #374151;background:#1f2937;color:#9ca3af;height:24px;"
                        >
                            @foreach (range(now()->year, 2024) as $y)
                                <option value="{{ $y }}" @selected((int)$y === $this->year)>{{ $y }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>

            <x-filament-apex-charts::chart
                :$chartId :$chartOptions :$contentHeight :$pollingInterval
                :$loadingIndicator :$darkMode :$deferLoading :$readyToLoad :$extraJsOptions
            />
        </div>
    </x-filament::card>
</x-filament-widgets::widget>
