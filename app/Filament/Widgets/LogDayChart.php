<?php

namespace App\Filament\Widgets;

use App\Models\Log;
use App\Models\Metergas;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LogDayChart extends ChartWidget
{
    protected static ?string $heading = 'Volume Per Hour Today';
    protected static ?string $pollingInterval = null;
    protected int | string | array $columnSpan = 'half';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $user_id = Auth::id();
        $metergas = Metergas::where('user_id', $user_id)->get();
        $data_log = [];

        // Membuat daftar jam dari 00:00 - 23:00
        $allHours = collect(range(0, 23))->map(fn($hour) => str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00')->toArray();

        if ($metergas->isNotEmpty()) {
            foreach ($metergas as $item) {
                // Ambil log untuk hari ini
                $logs = Log::where('metergas_id', $item->id)
                    ->whereBetween('created_at', [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()])
                    ->get()
                    ->groupBy(fn($log) => Carbon::parse($log->created_at)->format('H:00'))
                    ->map(fn($hourLogs) => $hourLogs->sum('volume'));

                // Inisialisasi array data dengan nilai 0 untuk semua jam
                $hourlyData = array_fill_keys($allHours, 0);

                // Masukkan data dari query ke dalam array jam yang sudah diinisialisasi
                foreach ($logs as $hour => $volume) {
                    $hourlyData[$hour] = $volume;
                }

                // Warna untuk dataset
                $color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));

                $data_log[] = [
                    'label' => 'Metergas ' . $item->serialNo,
                    'data' => array_values($hourlyData),
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
