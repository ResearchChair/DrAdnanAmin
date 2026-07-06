<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProfileResource\Pages\CreateProfile;
use App\Filament\Resources\ProfileResource\Pages\EditProfile;
use App\Filament\Resources\ProfileResource\Pages\ListProfiles;
use App\Models\Profile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
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
                Forms\Components\Toggle::make('is_active')
                    ->label('Active on website')
                    ->helperText('Only one profile can be active. The active profile is shown on the public site.')
                    ->default(true),
                Forms\Components\TextInput::make('credentials')->placeholder('Ph.D.'),
                Forms\Components\TextInput::make('title'),
                Forms\Components\TextInput::make('tagline'),
                Forms\Components\FileUpload::make('photo_path')
                    ->label('Profile photo')
                    ->image()
                    ->disk('public')
                    ->directory('profile')
                    ->visibility('public')
                    ->maxSize(5120)
                    ->helperText('JPEG or PNG, max 5 MB. On production run: php artisan portfolio:ensure-storage --link'),
            ])->columns(2),
            Forms\Components\Section::make('Curriculum Vitae')->schema([
                Forms\Components\FileUpload::make('cv_path')
                    ->label('CV file')
                    ->disk('public')
                    ->directory('cv')
                    ->visibility('public')
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ])
                    ->maxSize(10240)
                    ->helperText('PDF or Word document, max 10 MB. Set the download key under Site Settings.'),
                Forms\Components\TextInput::make('cv_label')
                    ->label('CV link label')
                    ->placeholder('Curriculum Vitae')
                    ->helperText('Shown on the download button, e.g. "Curriculum Vitae" or "Download CV".'),
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
                Forms\Components\RichEditor::make('bio_html')
                    ->label('Biographical sketch')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('research_interests')
                    ->label('Research interests')
                    ->rows(5)
                    ->helperText('One interest per line. Shown in the home page sidebar.')
                    ->columnSpanFull(),
            ]),
            Forms\Components\Section::make('Biography for Article')
                ->description('Article-style biography shown on the home page. Visitors click to open it in a modal with your photo.')
                ->schema([
                    Forms\Components\RichEditor::make('research_articles_html')
                        ->label('Biography for article content')
                        ->columnSpanFull(),
                ]),
            Forms\Components\Section::make('Flyer Highlights')
                ->description('Short snippets for flyers and brochures. Each highlight opens in a modal with your photo; visitors can copy the text from there.')
                ->schema([
                    Forms\Components\Repeater::make('flyer_highlights')
                        ->label('Highlights')
                        ->schema([
                            Forms\Components\TextInput::make('title')
                                ->placeholder('e.g. Research focus, Recent award')
                                ->maxLength(120),
                            Forms\Components\Textarea::make('content')
                                ->label('Highlight text')
                                ->rows(4)
                                ->required()
                                ->placeholder('Write plain text that colleagues can copy into flyers or event materials.'),
                        ])
                        ->defaultItems(0)
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? str($state['content'] ?? '')->limit(50)->toString() ?: 'Highlight')
                        ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('title')->limit(40),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('is_active', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Tables\Actions\DeleteAction $action, Profile $record) {
                        if (Profile::query()->count() <= 1) {
                            Notification::make()
                                ->title('Cannot delete the only profile')
                                ->danger()
                                ->send();

                            $action->halt();
                        }

                        if ($record->is_active) {
                            Notification::make()
                                ->title('Cannot delete the active profile')
                                ->body('Disable it or activate another profile first.')
                                ->danger()
                                ->send();

                            $action->halt();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Tables\Actions\DeleteBulkAction $action, $records) {
                            if (Profile::query()->count() - $records->count() < 1) {
                                Notification::make()
                                    ->title('Cannot delete all profiles')
                                    ->danger()
                                    ->send();

                                $action->halt();
                            }

                            if ($records->contains(fn (Profile $profile) => $profile->is_active)) {
                                Notification::make()
                                    ->title('Cannot delete the active profile')
                                    ->body('Disable it or activate another profile first.')
                                    ->danger()
                                    ->send();

                                $action->halt();
                            }
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProfiles::route('/'),
            'create' => CreateProfile::route('/create'),
            'edit' => EditProfile::route('/{record}/edit'),
        ];
    }
}
