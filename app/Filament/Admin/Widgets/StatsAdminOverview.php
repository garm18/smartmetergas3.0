<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Log;
use App\Models\User;
use App\Models\Metergas;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsAdminOverview extends BaseWidget
{
    protected function getStats(): array
    {   
        $someCondition = true;
        // Query to get users count grouped by month (or another time frame)
        $userCounts = User::select(DB::raw('MONTH(created_at) as month'), DB::raw('count(*) as count'))
                        ->groupBy('month')
                        ->orderBy('month')
                        ->pluck('count', 'month');

        // Fill in missing months with 0 (optional, for a smooth chart)
        $chartData = array_replace(array_fill(1, 12, 0), $userCounts->toArray());

        // Query to get MeterGas count grouped by month
        $meterGasCounts = Metergas::select(DB::raw('MONTH(created_at) as month'), DB::raw('count(*) as count'))
                        ->groupBy('month')
                        ->orderBy('month')
                        ->pluck('count', 'month');

        // Fill in missing months with 0
        $meterGasChartData = array_replace(array_fill(1, 12, 0), $meterGasCounts->toArray());

        // Query to get total gas volume (logs) grouped by month
        $logVolumeCounts = Log::select(DB::raw('MONTH(created_at) as month'), DB::raw('sum(volume) as total_volume'))
                        ->groupBy('month')
                        ->orderBy('month')
                        ->pluck('total_volume', 'month');

        // Fill in missing months with 0
        $logVolumeChartData = array_replace(array_fill(1, 12, 0), $logVolumeCounts->toArray());

        return [
            Stat::make('Total Users', User::query()->count())
                ->description('All users from database')
                ->descriptionIcon('heroicon-m-user-circle')
                ->chart(array_values($chartData))
                ->color('primary'),

            Stat::make('Total Meter Gas', Metergas::query()->count())
                ->description('All meter gas from database')
                ->descriptionIcon('heroicon-m-cog')
                ->chart(array_values($meterGasChartData))
                ->color('success'),

            Stat::make('Total Gas Consumption', Log::sum('volume'))
                ->description('All volume gas from database')
                ->descriptionIcon('heroicon-o-circle-stack')
                ->chart(array_values($logVolumeChartData))
                ->color('info'),
        ];
    }
}
