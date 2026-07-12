<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConsultancyEngagementResource\Pages;
use App\Models\ConsultancyEngagement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ConsultancyEngagementResource extends Resource
{
    protected static ?string $model = ConsultancyEngagement::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Professional';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Consultancy';

    protected static ?string $modelLabel = 'Consultancy engagement';

    protected static ?string $pluralModelLabel = 'Consultancy engagements';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->required()
                ->columnSpanFull()
                ->placeholder('e.g. AI strategy advisory for student analytics'),
            Forms\Components\TextInput::make('organization')
                ->required()
                ->placeholder('Client / organization name'),
            Forms\Components\Select::make('type')
                ->options(config('academic.consultancy_types'))
                ->required()
                ->native(false),
            Forms\Components\TextInput::make('role')
                ->default('Consultant')
                ->placeholder('Consultant, Advisor, Expert…'),
            Forms\Components\TextInput::make('year_start')->numeric()->label('Year start'),
            Forms\Components\TextInput::make('year_end')->numeric()->label('Year end')->helperText('Leave blank if ongoing or single year.'),
            Forms\Components\TextInput::make('location'),
            Forms\Components\TextInput::make('url')->url()->label('Related URL'),
            Forms\Components\Textarea::make('description')->rows(4)->columnSpanFull(),
            Forms\Components\Toggle::make('is_visible')->default(true),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('year_start', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('organization')->searchable()->limit(30),
                Tables\Columns\TextColumn::make('type')->badge()->formatStateUsing(
                    fn (?string $state): string => config('academic.consultancy_types.'.$state, (string) $state)
                ),
                Tables\Columns\TextColumn::make('year_start')->label('Years')
                    ->formatStateUsing(fn ($state, ConsultancyEngagement $record): string => $record->yearRangeLabel()),
                Tables\Columns\IconColumn::make('is_visible')->boolean(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->headerActions([Tables\Actions\CreateAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageConsultancyEngagements::route('/'),
        ];
    }
}
