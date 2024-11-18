<?php

namespace App\Filament\Widgets;

use App\Models\Log;
use App\Models\Metergas;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\TableWidget as BaseWidget;

class TableApp extends BaseWidget
{
    protected static ?string $heading = 'Table PerMonth';
    protected static ?int $sort = 5;

    public function table(Table $table): Table
    {
        // Get the metergas IDs associated with the logged-in user
        $userMetergasIds = Metergas::where('user_id', Auth::id())->pluck('id'); // Get the

        return $table // Get the metergas IDs associated with the
            ->query(
                // Filter Log records based on the user's metergas IDs
                Log::whereIn('metergas_id', $userMetergasIds)
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('serial'),
                Tables\Columns\TextColumn::make('volume'),
                Tables\Columns\IconColumn::make('condition_io')
                    ->label('condition')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Updated Volume')
                    ->dateTime('Y-m-d'),
            ]);
    }
}
