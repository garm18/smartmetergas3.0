<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Metergas;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Filament\Widgets\ChartWidget;

class MetergasAdminChart extends ChartWidget
{
    protected static ?string $heading = 'Total Meter Gas ';
    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $data = Trend::model(Metergas::class)
        ->between(
            start: now()->startOfYear(),
            end: now()->endOfYear(),
        )
        ->perMonth()
        ->count();
 
        return [
            'datasets' => [
                [
                    'label' => 'Meter Gas installed',
                    'data' => $data->map(fn (TrendValue $value) => (int) $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
