<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PublicationResource\Pages;
use App\Models\Publication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PublicationResource extends Resource
{
    protected static ?string $model = Publication::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Research';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required()->columnSpanFull(),
            Forms\Components\Select::make('type')
                ->options(config('academic.publication_types'))
                ->required()
                ->helperText('Use “In Progress” for manuscripts under preparation or review.'),
            Forms\Components\TextInput::make('year')->numeric(),
            Forms\Components\TextInput::make('venue')->label('Venue / journal / conference'),
            Forms\Components\TextInput::make('publisher')
                ->placeholder('e.g. IEEE, Springer, Elsevier')
                ->helperText('Optional. If empty, publisher is inferred from venue/DOI for the Summary tab.'),
            Forms\Components\Textarea::make('authors')->rows(2)->columnSpanFull(),
            Forms\Components\TextInput::make('doi'),
            Forms\Components\TextInput::make('url')->url(),
            Forms\Components\TextInput::make('pdf_url')->url(),
            Forms\Components\Textarea::make('abstract')->rows(4)->columnSpanFull(),
            Forms\Components\TextInput::make('citation_count')->numeric()->default(0),
            Forms\Components\Toggle::make('featured'),
            Forms\Components\Toggle::make('is_visible')->default(true),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('year', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('year')->sortable(),
                Tables\Columns\TextColumn::make('title')->searchable()->limit(50),
                Tables\Columns\TextColumn::make('type')->badge()->formatStateUsing(fn ($state) => config('academic.publication_types.'.$state, $state)),
                Tables\Columns\TextColumn::make('venue')->limit(30),
                Tables\Columns\TextColumn::make('publisher')->toggleable()->limit(20),
                Tables\Columns\TextColumn::make('citation_count')->label('Citations'),
                Tables\Columns\IconColumn::make('featured')->boolean(),
                Tables\Columns\IconColumn::make('is_visible')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options(config('academic.publication_types')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPublications::route('/'),
            'create' => Pages\CreatePublication::route('/create'),
            'edit' => Pages\EditPublication::route('/{record}/edit'),
        ];
    }
}
