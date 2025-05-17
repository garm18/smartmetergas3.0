<?php

namespace App\Filament\Admin\Resources;

use App\Models\Log;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Illuminate\Support\Facades\Response;
use App\Filament\Admin\Resources\LogResource\Pages;

class LogResource extends Resource
{
    protected static ?string $model = Log::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';
    protected static ?string $navigationLabel = 'Logs';
    protected static ?string $modelLabel = 'Log';
    protected static ?string $navigationGroup = 'Logs System';
    protected static ?string $slug = 'metergas-log';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('metergas_id')
                    ->relationship('metergas', 'id')
                    ->required(),
                Forms\Components\TextInput::make('volume')
                    ->required()
                    ->numeric(),
                Forms\Components\Select::make('type_io') //Choose between Onsite or remote
                    ->options([
                        'Onsite' => 'Onsite', //Tombol ditekan dilokasi
                        'Remote' => 'Remote', //Tombol ditekan via Web
                    ])
                    ->required(),
                Forms\Components\TextInput::make('battery')
                    ->required()
                    ->numeric(),
                Forms\Components\Toggle::make('condition_io')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('metergas_id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('imei')
                    ->label('IMEI'),
                Tables\Columns\TextColumn::make('owner'), //virtual tabel tidak bisa query
                Tables\Columns\TextColumn::make('serial'), //virtual tabel tidak bisa query
                Tables\Columns\TextColumn::make('volume')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('battery')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type_io')
                    ->sortable(),
                Tables\Columns\TextColumn::make('signal_strength')
                    ->label('rssi')
                    ->numeric(),
                Tables\Columns\TextColumn::make('signal_level')
                    ->label('signal level')
                    ->numeric(),
                Tables\Columns\IconColumn::make('condition_io')
                    ->boolean()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('export_csv')
                        ->label('Export CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function ($records) {
                            $filename = 'log_export_' . now()->format('Ymd_His') . '.csv';
                            $path = storage_path('app/' . $filename);

                            SimpleExcelWriter::create($path)
                                ->addRows(
                                    $records->map(fn ($log) => [
                                        'IMEI' => $log->imei,
                                        'Owner' => $log->owner,
                                        'Serial' => $log->serial,
                                        'Volume' => $log->volume,
                                        'Battery' => $log->battery,
                                        'Signal Strength' => $log->signal_strength,
                                        'Signal Level' => $log->signal_level,
                                        'Condition IO' => $log->condition_io ? 'Active' : 'Inactive',
                                        'Type IO' => $log->type_io,
                                        'Created At' => $log->created_at,
                                    ])
                                );

                            return Response::download($path)->deleteFileAfterSend();
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageLogs::route('/'),
        ];
    }
}
