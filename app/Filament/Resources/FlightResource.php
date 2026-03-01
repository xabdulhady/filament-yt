<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FlightResource\Pages;
use App\Filament\Resources\FlightResource\RelationManagers;
use App\Models\Flight;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Wizard;

class FlightResource extends Resource
{
    protected static ?string $model = Flight::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([

                    Wizard\Step::make('Flight Information')
                        ->schema([
                            Forms\Components\TextInput::make('flight_number')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),

                            Forms\Components\Select::make('airline_id')
                                ->relationship('airline', 'name')
                                ->required(),
                        ]),

                    Wizard\Step::make('Flight Segments')
                        ->schema([
                            Forms\Components\Repeater::make('flightSegments')
                                ->relationship('flightSegments')
                                ->schema([
                                    Forms\Components\TextInput::make('sequence')
                                        ->numeric()
                                        ->required(),

                                    Forms\Components\Select::make('airport_id')
                                        ->relationship('airport', 'name')
                                        ->required(),

                                    Forms\Components\DateTimePicker::make('time')
                                        ->required(),
                                ])
                                // ->grid(2)
                                ->collapsed()
                                ->minItems(1)
                                ->itemLabel(fn(array $state): ?string => 'Segments ' . $state['sequence'] ?? null),
                        ]),

                    Wizard\Step::make('Flight Class')
                        ->schema([
                            Forms\Components\Repeater::make('flightClasses')
                                ->relationship('flightClasses')
                                ->schema([
                                    Forms\Components\Select::make('class_type')
                                        ->options([
                                            'economy' => 'Economy',
                                            'business' => 'Business',
                                        ])
                                        ->required(),

                                    Forms\Components\TextInput::make('price')
                                        ->required()
                                        ->prefix('Rs ')
                                        ->numeric()
                                        ->minValue(1),

                                    Forms\Components\TextInput::make('total_seats')
                                        ->required()
                                        ->numeric()
                                        ->minValue(1)
                                        ->default(1),

                                    Forms\Components\Select::make('facilities')
                                        ->relationship('facilities', 'name')
                                        ->multiple()
                                        ->required(),
                                ])
                                ->collapsed()
                                ->minItems(1)
                                ->itemLabel(fn(array $state): ?string => ucfirst($state['class_type']) . ' Class' ?? null),
                        ]),

                ])->columnSpan(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('flight_number'),
                Tables\Columns\TextColumn::make('airline.name'),
                Tables\Columns\TextColumn::make('flightSegments')
                    ->label('Route & Duration')
                    ->formatStateUsing(function (Flight $record): string {
                        $firstSegment = $record->flightSegments->first();
                        $lastSegment = $record->flightSegments->last();
                        $route = $firstSegment->airport->iata_code . '-' . $lastSegment->airport->iata_code;
                        $duration = Carbon::parse($firstSegment->time)->isoFormat('LLL') . ' - ' . Carbon::parse($lastSegment->time)->isoFormat('LLL');
                        return $route . ' | ' . $duration;
                    }),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFlights::route('/'),
            'create' => Pages\CreateFlight::route('/create'),
            'edit' => Pages\EditFlight::route('/{record}/edit'),
        ];
    }
}
