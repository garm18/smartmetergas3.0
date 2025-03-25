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
        // Ambil jumlah total metergas yang sudah ada sebelum tahun ini
        $previousTotal = Metergas::where('created_at', '<', now()->startOfYear())->count();
    
        // Ambil data tren jumlah meter gas yang terinstall per bulan dalam tahun ini
        $data = Trend::model(Metergas::class)
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->count();
    
        // Ubah menjadi akumulasi total meter gas yang terinstall setiap bulan
        $cumulativeData = [];
        $total = $previousTotal; // Mulai dari jumlah sebelum tahun ini
        foreach ($data as $value) {
            $total += (int) $value->aggregate; // Menjumlahkan secara kumulatif
            $cumulativeData[] = $total;
        }
    
        return [
            'datasets' => [
                [
                    'label' => 'Meter Gas installed',
                    'data' => $cumulativeData,
                    'borderColor' => '#FFD700',
                    'backgroundColor' => 'rgba(255, 215, 0, 0.5)',
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
