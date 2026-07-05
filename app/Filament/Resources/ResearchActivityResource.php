<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResearchActivityResource\Pages;
use App\Models\ResearchActivity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ResearchActivityResource extends Resource
{
    protected static ?string $model = ResearchActivity::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Research';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Research Activities';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('type')->options(config('academic.activity_types'))->required(),
            Forms\Components\TextInput::make('title')->required()->columnSpanFull(),
            Forms\Components\TextInput::make('organization'),
            Forms\Components\TextInput::make('role'),
            Forms\Components\TextInput::make('year')->numeric(),
            Forms\Components\TextInput::make('year_end'),
            Forms\Components\TextInput::make('url')->url(),
            Forms\Components\Textarea::make('description')->rows(4)->columnSpanFull(),
            Forms\Components\Toggle::make('is_visible')->default(true),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('organization'),
                Tables\Columns\TextColumn::make('year'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->headerActions([Tables\Actions\CreateAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageResearchActivities::route('/'),
        ];
    }
}
