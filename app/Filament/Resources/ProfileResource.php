<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProfileResource\Pages\EditProfile;
use App\Filament\Resources\ProfileResource\Pages\ListProfiles;
use App\Models\Profile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProfileResource extends Resource
{
    protected static ?string $model = Profile::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = 'Site Content';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identity')->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('credentials')->placeholder('Ph.D.'),
                Forms\Components\TextInput::make('title'),
                Forms\Components\TextInput::make('tagline'),
                Forms\Components\FileUpload::make('photo_path')->image()->directory('profile')->visibility('public'),
            ])->columns(2),
            Forms\Components\Section::make('Affiliation & Contact')->schema([
                Forms\Components\TextInput::make('affiliation'),
                Forms\Components\TextInput::make('secondary_affiliation'),
                Forms\Components\TextInput::make('email')->email(),
                Forms\Components\TextInput::make('phone'),
                Forms\Components\TextInput::make('whatsapp')
                    ->label('WhatsApp number')
                    ->placeholder('+923001234567')
                    ->helperText('Include country code, e.g. +92 for Pakistan.'),
                Forms\Components\TextInput::make('location'),
            ])->columns(2),
            Forms\Components\Section::make('Academic IDs')->schema([
                Forms\Components\TextInput::make('orcid_id')
                    ->label('ORCID ID')
                    ->placeholder('0000-0002-1234-5678')
                    ->helperText('Saving your ORCID ID automatically imports and updates your publications.'),
                Forms\Components\TextInput::make('openalex_author_id')->label('OpenAlex Author ID'),
            ])->columns(2),
            Forms\Components\Section::make('Biography')->schema([
                Forms\Components\RichEditor::make('bio_html')->columnSpanFull(),
                Forms\Components\Textarea::make('research_interests')->rows(5)->columnSpanFull(),
            ]),
            Forms\Components\Section::make('Citation Stats')->relationship('citationStats')->schema([
                Forms\Components\TextInput::make('total_citations')->numeric(),
                Forms\Components\TextInput::make('h_index')->numeric(),
                Forms\Components\TextInput::make('i10_index')->numeric(),
                Forms\Components\TextInput::make('publication_count')->numeric(),
                Forms\Components\Select::make('source')->options([
                    'google_scholar' => 'Google Scholar',
                    'manual' => 'Manual',
                    'openalex' => 'OpenAlex',
                ]),
                Forms\Components\DateTimePicker::make('synced_at'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('title')->limit(40),
                Tables\Columns\TextColumn::make('email'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProfiles::route('/'),
            'edit' => EditProfile::route('/{record}/edit'),
        ];
    }
}
