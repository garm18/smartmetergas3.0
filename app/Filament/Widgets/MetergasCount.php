<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class MetergasCount extends Widget
{
    protected static ?string $pollingInterval = null;
    protected static ?int $sort = -2;
    public $metergasCount;
    public function mount(){
        $user=Auth::user();
        $this->metergasCount = $user->metergas->count();
    }
    protected static string $view = 'filament.widgets.metergas-count';
}
