<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MetergasResource\Pages;
use App\Filament\Admin\Resources\MetergasResource\RelationManagers;
use App\Models\Metergas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MetergasResource extends Resource
{
    protected static ?string $model = Metergas::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
