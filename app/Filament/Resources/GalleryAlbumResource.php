<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GalleryAlbumResource\Pages;
use App\Models\GalleryAlbum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GalleryAlbumResource extends Resource
{
    protected static ?string $model = GalleryAlbum::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Site Content';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Gallery';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required(),
            Forms\Components\Textarea::make('description')->rows(3),
            Forms\Components\FileUpload::make('cover_image')->image()->disk('public')->directory('gallery')->visibility('public'),
            Forms\Components\Toggle::make('is_visible')->default(true),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            Forms\Components\Repeater::make('images')
                ->relationship()
                ->schema([
                    Forms\Components\TextInput::make('title'),
                    Forms\Components\FileUpload::make('image_path')->image()->disk('public')->directory('gallery')->required()->visibility('public'),
                    Forms\Components\TextInput::make('caption'),
                    Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                    Forms\Components\Toggle::make('is_featured')
                        ->label('Feature on home page')
                        ->default(false),
                ])
                ->columnSpanFull()
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('images_count')->counts('images')->label('Images'),
                Tables\Columns\IconColumn::make('is_visible')->boolean(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->headerActions([Tables\Actions\CreateAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageGalleryAlbums::route('/'),
        ];
    }
}
