<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EarnedBadgeResource\Pages;
use App\Models\EarnedBadge;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EarnedBadgeResource extends Resource
{
    protected static ?string $model = EarnedBadge::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Site Content';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Badges & Certificates';

    protected static ?string $modelLabel = 'Badge / Certificate';

    protected static ?string $pluralModelLabel = 'Badges & Certificates';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->required()
                ->placeholder('Oracle Certified Professional')
                ->columnSpanFull(),
            Forms\Components\TextInput::make('issuer')
                ->placeholder('Oracle, Microsoft, etc.'),
            Forms\Components\TextInput::make('year')
                ->numeric()
                ->minValue(1900)
                ->maxValue(2100),
            Forms\Components\TextInput::make('url')
                ->label('Credential URL')
                ->url()
                ->placeholder('https://'),
            Forms\Components\FileUpload::make('logo_path')
                ->label('Badge logo')
                ->image()
                ->disk('public')
                ->directory('badges')
                ->visibility('public')
                ->maxSize(2048)
                ->helperText('Square or wide badge image, max 2 MB.')
                ->columnSpanFull(),
            Forms\Components\TextInput::make('sort_order')
                ->numeric()
                ->default(0)
                ->helperText('Lower numbers appear first on the home page.'),
            Forms\Components\Toggle::make('is_visible')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')->label('#')->sortable(),
                Tables\Columns\ImageColumn::make('logo_path')->label('Logo')->disk('public')->height(40),
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('issuer'),
                Tables\Columns\TextColumn::make('year'),
                Tables\Columns\IconColumn::make('is_visible')->boolean(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->headerActions([Tables\Actions\CreateAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageEarnedBadges::route('/'),
        ];
    }
}
