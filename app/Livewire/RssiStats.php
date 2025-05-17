<?php

namespace App\Livewire;

use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Http;

class RssiStats extends BaseWidget
{
    protected function getStats(): array
    {
        try {
            $response = Http::get('http://195.35.28.54:5005/predict-rssi');

            if (!$response->successful()) {
                return [
                    Stat::make('Error', 'Gagal ambil data')->description('API tidak merespons')
                ];
            }

            $json = $response->json();

            if (!isset($json['data']) || empty($json['data'])) {
                return [
                    Stat::make('Error', 'Data kosong')->description('Tidak ada data dari API')
                ];
            }

            $collection = collect($json['data']);

            // Ambil data dari 2 hari terakhir berdasarkan timestamp terakhir
            $lastTimestamp = Carbon::parse($collection->last()['timestamp']);
            $startDate = $lastTimestamp->copy()->subDays(2);

            $filtered = $collection->filter(function ($item) use ($startDate, $lastTimestamp) {
                $ts = Carbon::parse($item['timestamp']);
                return $ts->between($startDate, $lastTimestamp);
            })->values();

            if ($filtered->isEmpty()) {
                return [
                    Stat::make('Info', 'Tidak ada data')->description('Rentang waktu kosong')
                ];
            }

            $actual = $filtered->pluck('actual_rssi');
            $min = round($actual->min(), 2);
            $max = round($actual->max(), 2);
            $avg = round($actual->avg(), 2);

            return [
                Stat::make('Min RSSI', "{$min} dBm")
                    ->description("Nilai terendah dari data RSSI")
                    ->color('danger'),

                Stat::make('Max RSSI', "{$max} dBm")
                    ->description("Nilai tertinggi dari data RSSI")
                    ->color('success'),

                Stat::make('Avg RSSI', "{$avg} dBm")
                    ->description("Rata-rata sinyal dalam 2 hari terakhir")
                    ->color('warning'),
            ];
        } catch (\Exception $e) {
            return [
                Stat::make('Exception', 'Gagal')->description($e->getMessage())
            ];
        }
    }
}
