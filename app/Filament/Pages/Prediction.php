<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Http;

class Prediction extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar'; // Ikon di sidebar
    protected static ?string $navigationLabel = 'Prediction'; // Label di sidebar
    protected static ?string $slug = 'prediction'; // URL: /prediction
    protected static string $view = 'filament.pages.prediction'; // View Blade untuk halaman
}
