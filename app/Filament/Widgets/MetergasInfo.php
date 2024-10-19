<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

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
                $log = $item->logs()->orderBy('created_at', 'desc')->first(); //melakukan update dimana data paling terbaru ditampilkan
                $battery = $log ? $log->battery: "Null";
                $data[] = Stat::make('Metergas '.$item->serialNo, $log ? $log->volume:"Null")
                            ->description('Volume Gas | Battery ('. $battery. "%)");
            }
        }
        return $data;
    }
}
