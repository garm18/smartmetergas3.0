<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class BatteryPrediction extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-battery-50';
    protected static ?string $navigationLabel = 'Battery Prediction';
    protected static ?string $title = 'Battery Prediction Overview';
    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.battery-prediction';

    public function getHeading(): string
    {
        return 'Battery Prediction Over Time';
    }
}