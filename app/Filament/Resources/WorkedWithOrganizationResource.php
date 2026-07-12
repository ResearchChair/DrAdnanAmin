<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkedWithOrganizationResource\Pages;
use App\Models\WorkedWithOrganization;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WorkedWithOrganizationResource extends Resource
{
    protected static ?string $model = WorkedWithOrganization::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Site Content';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationLabel = 'Worked With';

    protected static ?string $modelLabel = 'Organization';

    protected static ?string $pluralModelLabel = 'Worked With organizations';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->label('Title')
                ->placeholder('e.g. World Bank, Maxwell Stamp UK')
                ->helperText('Used in admin; optional to show on the public site.'),
            Forms\Components\Toggle::make('show_title')
                ->label('Show title with logo')
                ->helperText('Off = logo only on the home page.')
                ->default(true),
            Forms\Components\FileUpload::make('logo_path')
                ->label('Logo')
                ->image()
                ->disk('public')
                ->directory('worked-with')
                ->visibility('public')
                ->imagePreviewHeight('80')
                ->helperText('Prefer a clear logo on a transparent or light background.'),
            Forms\Components\TextInput::make('url')
                ->url()
                ->label('Website URL (optional)'),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            Forms\Components\Toggle::make('is_visible')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('logo_path')
                    ->label('Logo')
                    ->disk('public')
                    ->height(36),
                Tables\Columns\TextColumn::make('name')->label('Title')->searchable(),
                Tables\Columns\IconColumn::make('show_title')->label('Show title')->boolean(),
                Tables\Columns\IconColumn::make('is_visible')->boolean(),
                Tables\Columns\TextColumn::make('sort_order'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->headerActions([Tables\Actions\CreateAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageWorkedWithOrganizations::route('/'),
        ];
    }
}
