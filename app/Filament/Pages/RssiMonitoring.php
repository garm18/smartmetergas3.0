<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class RssiMonitoring extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-rss'; // icon sidebar
    protected static ?string $navigationLabel = 'RSSI Monitoring'; // label sidebar
    protected static ?string $slug = 'rssi-monitoring'; // URL: /rssi-prediction
    protected static string $view = 'filament.pages.rssi-monitoring';

    // protected function getViewData(): array
    // {
    //     return [
    //         'livewireComponent' => \App\Livewire\RssiChartPage::class
    //     ];
    // }
}
