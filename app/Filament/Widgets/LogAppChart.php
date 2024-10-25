<?php

namespace App\Filament\Widgets;

use App\Models\Log;
use App\Models\Metergas;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Str; // To use the random color function

class LogAppChart extends ChartWidget
{
    protected static ?string $heading = 'Volume PerMonth';
    protected static ?string $pollingInterval = null;
    protected static ?string $maxHeight = '300px';
    protected int | string | array $columnSpan = 'full';
    protected function getData(): array
    {
        $user_id = Auth::user()->id;
        $metergas = Metergas::where('user_id', $user_id)
            ->get();

        $data_log = [];
        $period = CarbonPeriod::create(Carbon::now()->subMonth()->startOfDay(), '1 day', Carbon::now()->startOfDay());
        $allDates = iterator_to_array($period);

        $allDates = array_map(fn ($date) => $date->format('Y-m-d'), $allDates);

        if ($metergas->isNotEmpty()) {
            foreach ($metergas as $item) {
                $logs = Log::where('metergas_id', $item->id)
                    ->whereDate('created_at', '>=', Carbon::now()->subMonth())
                    ->get()
                    ->groupBy(function ($date) {
                        return Carbon::parse($date->created_at)->format('Y-m-d');
                    })
                    ->map(function ($dayLogs) {
                        return $dayLogs->sum('volume');
                    });

                $dailyData = [];
                foreach ($allDates as $date) {
                    $dailyData[] = $logs->get($date, 0);
                }

                // Generate a random color for each dataset
                $color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));

                $data_log[] = [
                    'label' => 'Metergas ' . $item->serialNo,
                    'data' => $dailyData,
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                ];
            }
        }

        return [
            'datasets' => $data_log,
            'labels' => $allDates,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}