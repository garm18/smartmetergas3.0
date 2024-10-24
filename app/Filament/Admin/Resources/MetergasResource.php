<?php

namespace App\Filament\Admin\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Regency;
use App\Models\Village;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\District;
use App\Models\Metergas;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Admin\Resources\MetergasResource\Pages;
use App\Filament\Admin\Resources\MetergasResource\RelationManagers;

class MetergasResource extends Resource
{
    protected static ?string $model = Metergas::class;

    protected static ?string $navigationIcon = 'heroicon-o-signal';
    protected static ?string $navigationLabel = 'Metergas';
    protected static ?string $modelLabel = 'Meter Gas';
    protected static ?string $navigationGroup = 'Logs System';
    protected static ?string $slug = 'metergas-data';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('serialNo')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('connectivity')
                    ->required()
                    ->options([
                        'NB-IoT' => 'NB-IoT',
                        'LoRaWAN' => 'LoRaWAN',
                        'ZigFox' => 'ZigFox',
                    ])->native(false),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->native(false)
                    ->preload()
                    ->searchable(),

                Forms\Components\Select::make('province_id')
                    ->relationship(name:'province', titleAttribute:'name')
                    ->searchable()
                    ->preload()
                    ->live() //agar form menjadi reaktif
                    ->required(),

                Forms\Components\Select::make('regency_id')
                    ->options(function (Get $get): Collection { //option pada kode ini berguna untuk meringankan Website tidak bekerja berat
                        return Regency::query() //memilih query pada tabel province
                            ->where('province_id', $get('province_id')) // dimanana province_id akan mengambil data dari province_id
                            ->pluck('name', 'id');
                    })
                    ->label('Regency')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('district_id',null); //callback
                        $set('village_id',null); //callback
                    })
                    ->required(),

                Forms\Components\Select::make('district_id')
                    ->options(function (Get $get): Collection { //option pada kode ini berguna untuk meringankan Website tidak bekerja berat
                        return District::query() //memilih query pada tabel district
                            ->where('regency_id', $get('regency_id')) // dimanana regency_id akan mengambil data dari regency_id
                            ->pluck('name', 'id');
                    })
                    ->label('District')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('village_id',null); //callback
                    })
                    ->required(),

                Forms\Components\Select::make('village_id')
                    ->options(function (Get $get): Collection { //option pada kode ini berguna untuk meringankan Website tidak bekerja berat
                        return Village::query() //memilih query pada tabel village
                            ->where('district_id', $get('district_id')) // dimanana ditrict_id akan mengambil data dari district_id
                            ->pluck('name', 'id');
                    })
                    ->label('Village')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('serialNo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('connectivity')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('province.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('regency.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('district.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('village.name')
                    ->searchable(),
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMetergas::route('/'),
        ];
    }
}
