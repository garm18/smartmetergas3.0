<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use App\Models\Metergas;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\TableWidget as BaseWidget;

class TableValveStatus extends BaseWidget
{
    protected static ?string $heading = 'Table Valve Status';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 1;
    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Filter Metergas
                Metergas::where('user_id', Auth::id())
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('serialNo'),
                Tables\Columns\IconColumn::make('valve_status')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->actions([
                Tables\Actions\Action::make('valve_status')
                ->requiresConfirmation()
                ->modalHeading(fn($record)=>$record->valve_status ? 'CLOSED' : 'OPEN')
                ->action(function($record){
                    $record->valve_status =!$record->valve_status; // Set valve status
                    $record->save(); // Save record
                    if ($record->logs()->exists()) { // Check
                        $lastLog= $record->logs()->orderBy('created_at', 'desc')->first();
                        $record->logs()->create([
                            'condition_io' => $record->valve_status,
                            'volume' => $lastLog->volume,
                            'type_io' => 'remote',
                            'battery' => $lastLog->battery,
                            'metergas_id' => $record->id,
                        ]);
                    }
                    else{
                        $record->logs()->create([
                            'condition_io' => $record->valve_status,
                            'volume' => null,
                            'type_io' => 'remote',
                            'battery' => null,
                            'metergas_id' => $record->id,
                        ]);
                    }
                }),
            ]);
    }
}
