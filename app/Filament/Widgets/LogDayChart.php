<?php

namespace App\Filament\Widgets;

use App\Models\Log;
use App\Models\Metergas;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Str;

class LogDayChart extends ChartWidget
{
    protected static ?string $heading = 'Volume Per Hour Today';
    protected static ?string $pollingInterval = null;
    protected int | string | array $columnSpan = 'half';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $user_id = Auth::user()->id;
        $metergas = Metergas::where('user_id', $user_id)->get();
        $data_log = [];

        // Set period for today's date in hourly intervals
        $period = CarbonPeriod::create(Carbon::today(), '1 hour', Carbon::now());
        $allHours = iterator_to_array($period);
        $allHours = array_map(fn($date) => $date->format('H:00'), $allHours);

        if ($metergas->isNotEmpty()) {
            foreach ($metergas as $item) {
                $logs = Log::where('metergas_id', $item->id)
                    ->whereDate('created_at', Carbon::today())
                    ->get()
                    ->groupBy(function ($log) {
                        return Carbon::parse($log->created_at)->format('H:00');
                    })
                    ->map(function ($hourLogs) {
                        return $hourLogs->sum('volume');
                    });

                $hourlyData = [];
                foreach ($allHours as $hour) {
                    $hourlyData[] = $logs->get($hour, 0);
                }

                // Generate a random color for each dataset
                $color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));

                $data_log[] = [
                    'label' => 'Metergas ' . $item->serialNo,
                    'data' => $hourlyData,
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                ];
            }
        }

        return [
            'datasets' => $data_log,
            'labels' => $allHours,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
