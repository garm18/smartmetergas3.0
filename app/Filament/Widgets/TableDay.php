<?php

namespace App\Filament\Widgets;

use App\Models\Log;
use Filament\Tables;
use App\Models\Metergas;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Filters\SelectFilter;
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
                Log::whereIn('metergas_id', $userMetergasIds)
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('serial')
                    ->label('Serial No')
                    ->getStateUsing(fn (Log $record) => optional($record->metergas)->serialNo)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('volume')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('condition_io')
                    ->label('Condition')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Updated Volume')
                    ->dateTime('Y-m-d H:i:s')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('condition_io')
                    ->label('Condition')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),
                SelectFilter::make('metergas_id')
                    ->label('Serial No')
                    ->options(
                        Metergas::where('user_id', Auth::id())
                            ->pluck('serialNo', 'id')
                    ),
            ]);
    }
}
