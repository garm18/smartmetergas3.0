<?php

namespace App\Livewire;

use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Http;

class RssiChart extends ChartWidget
{
    protected static ?string $heading = 'Indikator Monitoring RSSI';

    public static function fetchChartData()
    {
        try {
            $response = Http::get('http://195.35.28.54:8080/load-data');
            $status = $response->status();
            $body = $response->body();
            $data = $response->json();

            //dd(compact('status', 'body', 'data'));

            // Validasi data
            if (isset($data['data'])) {
                $timestamps = array_column($data['data'], 'timestamp');
                $actual = array_column($data['data'], 'actual_rssi');
                $predict = array_column($data['data'], 'predicted_rssi');

                // Buat data chart berdasarkan statistik
                $chartData = [
                    'labels' => $timestamps,
                    'datasets' => [
                        [
                            'label' => 'RSSI Actual (dBm)',
                            'data' => $actual,
                            'borderColor' => 'blue',
                            'backgroundColor' => 'rgba(0, 0, 255, 0.2)',
                        ],
                        [
                            'label' => 'RSSI Predict (dBm)',
                            'data' => $predict,
                            'borderColor' => 'red',
                            'backgroundColor' => 'rgba(0, 255, 0, 0.2)',
                        ],
                    ],
                ];
            } else {
                $chartData = ['error' => 'Data RSSI tidak valid atau kosong'];
            }
        } catch (\Exception $e) {
            $chartData = ['error' => 'Gagal mengambil data dari API: ' . $e->getMessage()];
        }

        return $chartData;
    }

    protected function getData(): array
    {
        return self::fetchChartData();
    }

    protected function getType(): string
    {
        return 'line';
    }
}