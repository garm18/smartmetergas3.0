<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Log;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Filament\Widgets\ChartWidget;

class LogAdminChart extends ChartWidget
{
    protected static ?string $heading = 'Total Volume Gas';
    protected static string $color = 'info';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = Trend::model(Log::class)
        ->between(
            start: now()->startOfYear(),
            end: now()->endOfYear(),
        )
        ->perMonth()
        ->sum('volume');
 
        return [
            'datasets' => [
                [
                    'label' => 'Gas Consumption',
                    'data' => $data->map(fn (TrendValue $value) => (float) $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

}
