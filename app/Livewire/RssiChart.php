<?php

namespace App\Livewire;

use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Http;

class RssiChart extends ChartWidget
{
    protected static ?string $heading = 'Monitoring RSSI';
    protected int $dayRange = 2;
    protected static ?string $pollingInterval = '30s';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'title' => ['display' => true, 'text' => 'Signal Strength (dBm)'],
                    'suggestedMin' => -100,
                    'suggestedMax' => -40,
                ],
                'x' => [
                    'title' => ['display' => true, 'text' => 'Timestamp'],
                    'ticks' => ['maxRotation' => 90, 'minRotation' => 45],
                ],
            ],
            'plugins' => [
                'legend' => ['position' => 'bottom'],
                'tooltip' => ['mode' => 'index', 'intersect' => false],
            ],
            'elements' => [
                'line' => ['tension' => 0.2],
                'point' => ['radius' => 2, 'hoverRadius' => 5],
            ],
        ];
    }

    protected function getData(): array
    {
        $data = self::fetchChartData();

        if (isset($data['error'])) {
            return [
                'labels' => ['Error'],
                'datasets' => [
                    [
                        'label' => $data['error'],
                        'data' => [0],
                        'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
                        'borderColor' => 'rgba(239, 68, 68, 1)',
                    ],
                ],
            ];
        }

        return $data;
    }

    public function fetchChartData()
    {
        try {
            $response = Http::get('http://195.35.28.54:8080/predict-rssi');

            if (!$response->successful()) {
                return ['error' => 'API tidak merespons'];
            }

            $json = $response->json();

            if (!isset($json['data']) || empty($json['data'])) {
                return ['error' => 'Data kosong dari API'];
            }

            $collection = collect($json['data']);
            $lastTimestamp = Carbon::parse($collection->last()['timestamp']);
            $startDate = $lastTimestamp->copy()->subDays($this->dayRange);

            $filtered = $collection->filter(function ($item) use ($startDate, $lastTimestamp) {
                $ts = Carbon::parse($item['timestamp']);
                return $ts->between($startDate, $lastTimestamp);
            })->values();

            if ($filtered->isEmpty()) {
                return ['error' => 'Tidak ada data dalam rentang waktu'];
            }

            $timestamps = $filtered->map(fn($i) => Carbon::parse($i['timestamp'])->format('Y-m-d H:00'));
            $actual = $filtered->pluck('actual_rssi');
            $predict = $filtered->pluck('predicted_rssi');

            $goodLine = array_fill(0, count($actual), -65);
            $poorLine = array_fill(0, count($actual), -85);

            return [
                'labels' => $timestamps,
                'datasets' => [
                    [
                        'label' => 'RSSI Actual (dBm)',
                        'data' => $actual,
                        'borderColor' => 'rgba(59, 130, 246, 0.8)',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'fill' => true,
                        'borderWidth' => 2,
                    ],
                    [
                        'label' => 'RSSI Predict (dBm)',
                        'data' => $predict,
                        'borderColor' => 'rgba(239, 68, 68, 0.8)',
                        'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                        'borderDash' => [5, 5],
                        'fill' => false,
                        'borderWidth' => 2,
                    ],
                    [
                        'label' => 'Good Signal (-65 dBm)',
                        'data' => $goodLine,
                        'borderColor' => 'rgba(34, 197, 94, 0.7)',
                        'borderDash' => [2, 2],
                        'fill' => false,
                        'pointRadius' => 0,
                    ],
                    [
                        'label' => 'Poor Signal (-85 dBm)',
                        'data' => $poorLine,
                        'borderColor' => 'rgba(245, 158, 11, 0.7)',
                        'borderDash' => [2, 2],
                        'fill' => false,
                        'pointRadius' => 0,
                    ],
                ],
            ];
        } catch (\Exception $e) {
            return ['error' => 'Gagal mengambil data: ' . $e->getMessage()];
        }
    }

    protected function getFooter(): string
    {
        // Footer dikosongkan agar fokus pada chart saja
        return '';
    }
}
