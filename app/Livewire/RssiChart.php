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
                'annotation' => [
                    'annotations' => [
                        'forecastArea' => [
                            'type' => 'box',
                            'xMin' => 'forecastStart', // Dynamic nanti saat load data
                            'backgroundColor' => 'rgba(168, 85, 247, 0.08)',
                            'borderWidth' => 0,
                        ],
                    ],
                ],
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
            // 🔹 Ambil data aktual & prediksi
            $response = Http::get('http://195.35.28.54:5005/predict-rssi');
            $forecastResponse = Http::get('http://195.35.28.54:5005/forecast-rssi?minutes=30');

            if (!$response->successful() || !$forecastResponse->successful()) {
                return ['error' => 'API tidak merespons'];
            }

            $json = $response->json();
            $forecastJson = $forecastResponse->json();

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

            // 🔹 Data histori
            $timestamps = $filtered->map(fn($i) => Carbon::parse($i['timestamp'])->format('Y-m-d H:i'));
            $actual = $filtered->pluck('actual_rssi');
            $predict = $filtered->pluck('predicted_rssi');

            // 🔹 Data forecast
            $forecastCollection = collect($forecastJson['forecast'] ?? []);
            $forecastTimestamps = $forecastCollection->pluck('timestamp')->map(fn($i) => Carbon::parse($i)->format('Y-m-d H:i'));
            $forecastValues = $forecastCollection->pluck('forecasted_rssi');

            // 🔹 Gabung timestamp dan data
            $fullTimestamps = $timestamps->merge($forecastTimestamps)->values();
            $forecastDataAligned = array_merge(array_fill(0, $actual->count(), null), $forecastValues->toArray());

            // 🔹 Good/Poor Signal Lines
            $goodLine = array_fill(0, $fullTimestamps->count(), -65);
            $poorLine = array_fill(0, $fullTimestamps->count(), -85);

            return [
                'labels' => $fullTimestamps,
                'datasets' => [
                    [
                        'label' => 'RSSI Actual (dBm)',
                        'data' => $actual->merge(array_fill(0, $forecastValues->count(), null)),
                        'borderColor' => 'rgba(59, 130, 246, 0.8)',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'fill' => true,
                        'borderWidth' => 2,
                    ],
                    [
                        'label' => 'RSSI Predict (dBm)',
                        'data' => $predict->merge(array_fill(0, $forecastValues->count(), null)),
                        'borderColor' => 'rgba(239, 68, 68, 0.8)',
                        'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                        'borderDash' => [5, 5],
                        'fill' => false,
                        'borderWidth' => 2,
                    ],
                    [
                        'label' => 'Forecast RSSI (dBm)',
                        'data' => $forecastDataAligned,
                        'borderColor' => 'rgba(168, 85, 247, 0.9)', // ungu
                        'backgroundColor' => 'rgba(168, 85, 247, 0.1)',
                        'borderDash' => [1, 4],
                        'fill' => false,
                        'borderWidth' => 2,
                        'pointBackgroundColor' => array_map(function ($value) {
                            return $value !== null && $value <= -85 ? 'rgba(239, 68, 68, 1)' : 'rgba(168, 85, 247, 1)';
                        }, $forecastDataAligned),
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
        return '';
    }
}
