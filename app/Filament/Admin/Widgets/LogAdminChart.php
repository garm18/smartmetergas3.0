<?php

namespace App\Filament\Admin\Widgets;

use Carbon\Carbon;
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
        // Cari tanggal paling awal ada log gas
        $firstLog = Log::orderBy('created_at')->first();
    
        // Jika belum ada log, tampilkan data kosong
        if (!$firstLog) {
            return [
                'datasets' => [
                    [
                        'label' => 'Gas Consumption',
                        'data' => [],
                    ],
                ],
                'labels' => [],
            ];
        }
    
        // Ambil tahun pertama kali ada log
        $startYear = Carbon::parse($firstLog->created_at)->startOfYear();
    
        // Ambil data dari tahun pertama ada log sampai sekarang
        $data = Trend::model(Log::class)
            ->between(
                start: $startYear, // Mulai dari tahun pertama ada data
                end: now()->endOfYear(), // Hingga akhir tahun ini
            )
            ->perMonth()
            ->sum('volume');
    
        return [
            'datasets' => [
                [
                    'label' => 'Gas Consumption',
                    'data' => $data->map(fn (TrendValue $value) => (float) $value->aggregate),
                    'borderColor' => '#007bff',
                    'backgroundColor' => 'rgba(0, 123, 255, 0.5)',
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
