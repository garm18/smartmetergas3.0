<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LogResource\Pages;
use App\Filament\Admin\Resources\LogResource\RelationManagers;
use App\Models\Log;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
            ]);
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //     ]),
            // ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageLogs::route('/'),
        ];
    }
}
