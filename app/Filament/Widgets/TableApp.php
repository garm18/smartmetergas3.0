<?php

namespace App\Filament\Widgets;

use App\Models\Log;
use Filament\Tables;
use App\Models\Metergas;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Filament\Widgets\TableWidget as BaseWidget;

class TableApp extends BaseWidget
{
    protected static ?string $heading = 'Table PerMonth';
    protected static ?int $sort = 5;

    public function table(Table $table): Table
    {
        $userMetergasIds = Metergas::where('user_id', Auth::id())->pluck('id');

        return $table
            ->query(
                Log::whereIn('metergas_id', $userMetergasIds)
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('serial')->toggleable(),
                Tables\Columns\TextColumn::make('volume')->toggleable(),
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
                Filter::make('month')
                    ->form([
                        DatePicker::make('date')
                            ->label('Select Month')
                            ->displayFormat('F Y')
                            ->native(false),
                    ])
                    ->query(function ($query, array $data) {
                        if (!empty($data['date'])) {
                            $date = \Carbon\Carbon::parse($data['date']);
                            $query->whereMonth('created_at', $date->month)
                                  ->whereYear('created_at', $date->year);
                        }
                    }),

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
            ])
            ->columnToggleFormColumns(2);
    }
}
