<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SocialLinkResource\Pages;
use App\Models\SocialLink;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SocialLinkResource extends Resource
{
    protected static ?string $model = SocialLink::class;

    protected static ?string $navigationIcon = 'heroicon-o-share';

    protected static ?string $navigationGroup = 'Site Content';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Social Links';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('profile_id')
                ->relationship('profile', 'name')
                ->default(fn () => \App\Models\Profile::query()->value('id'))
                ->required(),
            Forms\Components\Select::make('platform')->options(config('academic.social_platforms'))->required(),
            Forms\Components\TextInput::make('label'),
            Forms\Components\TextInput::make('url')->url()->required(),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            Forms\Components\Toggle::make('is_visible')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('platform'),
                Tables\Columns\TextColumn::make('url'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->headerActions([Tables\Actions\CreateAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSocialLinks::route('/'),
        ];
    }
}
