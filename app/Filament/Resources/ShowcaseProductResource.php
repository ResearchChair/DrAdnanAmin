<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShowcaseProductResource\Pages;
use App\Models\ShowcaseProduct;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShowcaseProductResource extends Resource
{
    protected static ?string $model = ShowcaseProduct::class;

    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';

    protected static ?string $navigationGroup = 'Site Content';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Products & Initiatives';

    protected static ?string $modelLabel = 'Product';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('tagline')->placeholder('e.g. Digital Learning Platform'),
            Forms\Components\Textarea::make('description')->rows(4)->columnSpanFull(),
            Forms\Components\TextInput::make('url')->url()->label('Website URL'),
            Forms\Components\FileUpload::make('logo_path')->image()->directory('products')->visibility('public'),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            Forms\Components\Toggle::make('is_visible')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('tagline')->limit(40),
                Tables\Columns\IconColumn::make('is_visible')->boolean(),
                Tables\Columns\TextColumn::make('sort_order'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->headerActions([Tables\Actions\CreateAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageShowcaseProducts::route('/'),
        ];
    }
}
