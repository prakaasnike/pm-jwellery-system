@php
    $chartId = $this->getChartId();
    $chartOptions = $this->getOptions();
    $contentHeight = $this->getContentHeight();
    $pollingInterval = $this->getPollingInterval();
    $loadingIndicator = $this->getLoadingIndicator();
    $deferLoading = $this->getDeferLoading();
    $readyToLoad = $this->readyToLoad;
    $darkMode = $this->getDarkMode();
    $extraJsOptions = $this->extraJsOptions();
    $periods = ['1D', '7D', '30D', '1Y'];
@endphp

<x-filament-widgets::widget class="filament-widgets-chart-widget filament-apex-charts-widget">
    <x-filament::card class="filament-apex-charts-card">
        <div x-data="{ dropdownOpen: false }">
            <div class="flex items-center justify-between gap-4 py-2">
                <div class="text-base font-semibold leading-6">
                    {{ $this->getHeading() }}
                </div>

                <div class="flex flex-wrap items-center justify-end gap-1">
                    @foreach ($periods as $period)
                        <button
                            type="button"
                            wire:click="setPeriod('{{ $period }}')"
                            style="height:24px;padding:2px 10px;font-size:11px;font-weight:600;border-radius:5px;cursor:pointer;
                                {{ $this->period === $period
                                    ? 'background:#3b82f6;color:#fff;border:1px solid #3b82f6;'
                                    : 'background:transparent;color:#9ca3af;border:1px solid #374151;' }}"
                        >
                            {{ $period }}
                        </button>
                    @endforeach

                    @if ($this->period === '1Y')
                        <select
                            wire:model.live="year"
                            style="margin-left:6px;font-size:12px;padding:1px 6px;border-radius:5px;border:1px solid #374151;background:#1f2937;color:#9ca3af;height:24px;"
                        >
                            @foreach ($this->getAvailableYears() as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>

            <x-filament-apex-charts::chart
                :$chartId
                :$chartOptions
                :$contentHeight
                :$pollingInterval
                :$loadingIndicator
                :$darkMode
                :$deferLoading
                :$readyToLoad
                :$extraJsOptions
            />
        </div>
    </x-filament::card>
</x-filament-widgets::widget>
