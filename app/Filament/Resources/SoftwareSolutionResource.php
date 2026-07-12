<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SoftwareSolutionResource\Pages;
use App\Models\SoftwareSolution;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SoftwareSolutionResource extends Resource
{
    protected static ?string $model = SoftwareSolution::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationGroup = 'Professional';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Software Solutions';

    protected static ?string $modelLabel = 'Software solution';

    protected static ?string $pluralModelLabel = 'Software solutions';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->columnSpanFull()
                ->placeholder('e.g. Research Chair Management System'),
            Forms\Components\TextInput::make('organization')
                ->required()
                ->label('Client / organization')
                ->placeholder('Organization the solution was built for'),
            Forms\Components\TextInput::make('tagline')
                ->placeholder('Short one-line description'),
            Forms\Components\Select::make('type')
                ->options(config('academic.software_solution_types'))
                ->required()
                ->native(false),
            Forms\Components\TextInput::make('year')->numeric(),
            Forms\Components\TextInput::make('tech_stack')
                ->label('Tech stack')
                ->placeholder('Laravel, MySQL, Vue — comma-separated')
                ->columnSpanFull(),
            Forms\Components\TextInput::make('url')->url()->label('Demo / product URL'),
            Forms\Components\FileUpload::make('logo_path')
                ->image()
                ->disk('public')
                ->directory('software-solutions')
                ->visibility('public')
                ->label('Logo (optional)'),
            Forms\Components\Textarea::make('description')->rows(4)->columnSpanFull(),
            Forms\Components\Toggle::make('is_visible')->default(true),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('year', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('organization')->searchable()->limit(30),
                Tables\Columns\TextColumn::make('type')->badge()->formatStateUsing(
                    fn (?string $state): string => config('academic.software_solution_types.'.$state, (string) $state)
                ),
                Tables\Columns\TextColumn::make('year'),
                Tables\Columns\IconColumn::make('is_visible')->boolean(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->headerActions([Tables\Actions\CreateAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSoftwareSolutions::route('/'),
        ];
    }
}
