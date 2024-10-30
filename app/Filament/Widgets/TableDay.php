<?php

namespace App\Filament\Widgets;

use App\Models\Log;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TableDay extends BaseWidget
{
    protected static ?string $heading = 'Table PerDay';
    protected static ?int $sort = 3;
    public function table(Table $table): Table
    {
        return $table
            ->query(Log::query())
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
