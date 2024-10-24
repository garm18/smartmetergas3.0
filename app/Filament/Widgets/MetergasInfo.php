<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\HtmlString;

class MetergasInfo extends BaseWidget
{
    protected static ?string $pollingInterval = null;
    protected static ?int $sort = -2;
    protected function getStats(): array
    {
        $user=Auth::user();
        $metergas = $user->metergas;

        $data = [];
        if ($metergas->count()){
            foreach ($metergas as $item){
                $totalVolume = $item->logs()->sum('volume');
                $latestLog = $item->logs()->latest()->first();

                $battery = $latestLog ? $latestLog->battery : "Null";
                $volume = $totalVolume ?: "Null";

                // Determine battery color based on the percentage
                if ($battery > 75) {
                    $batteryColor = 'green';
                } elseif ($battery > 35 && $battery <= 75) {
                    $batteryColor = 'yellow';
                } elseif ($battery >= 0 && $battery <= 35) {
                    $batteryColor = 'red';
                } else {
                    $batteryColor = 'black'; // fallback if battery is not within range
                }

                // Highlight battery value based on the calculated color
                $batteryHighlight = '<span style="color: ' . $batteryColor . '; font-weight: bold;">' . $battery . '%</span>';

                $data[] = Stat::make('Metergas '.$item->serialNo, $volume)
                            ->description(new HtmlString('Volume Gas (m<sup>3</sup>) | Battery ('. $batteryHighlight. ')'));
            }
        }
        return $data;
    }
}
