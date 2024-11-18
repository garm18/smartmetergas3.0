<?php

namespace App\Filament\Widgets;

use App\Models\Log;
use Filament\Tables;
use App\Models\Metergas;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\TableWidget as BaseWidget;

class TableDay extends BaseWidget
{
    protected static ?string $heading = 'Table PerDay';
    protected static ?int $sort = 4;
    public function table(Table $table): Table
    {
        $userMetergasIds = Metergas::where('user_id', Auth::id())->pluck('id');

        return $table
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
                    ->dateTime('H:i:s'),
            ]);
    }
}
