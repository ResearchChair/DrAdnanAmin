<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrainingSessionResource\Pages;
use App\Models\TrainingSession;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TrainingSessionResource extends Resource
{
    protected static ?string $model = TrainingSession::class;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected static ?string $navigationGroup = 'Professional';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Training & Facilitation';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required()->columnSpanFull(),
            Forms\Components\Select::make('type')->options(config('academic.training_types'))->required(),
            Forms\Components\TextInput::make('event_name'),
            Forms\Components\TextInput::make('organization'),
            Forms\Components\TextInput::make('role')->default('Resource Person'),
            Forms\Components\TextInput::make('year')->numeric(),
            Forms\Components\TextInput::make('location'),
            Forms\Components\TextInput::make('materials_url')->url(),
            Forms\Components\Textarea::make('description')->rows(3)->columnSpanFull(),
            Forms\Components\Toggle::make('is_visible')->default(true),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('role'),
                Tables\Columns\TextColumn::make('year'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->headerActions([Tables\Actions\CreateAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTrainingSessions::route('/'),
        ];
    }
}
