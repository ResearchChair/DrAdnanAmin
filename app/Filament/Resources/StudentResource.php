<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Models\Publication;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Research';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Student')->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\FileUpload::make('photo_path')
                    ->label('Photo')
                    ->image()
                    ->disk('public')
                    ->directory('students')
                    ->visibility('public')
                    ->maxSize(5120)
                    ->helperText('JPEG or PNG, max 5 MB. On production run: php artisan portfolio:ensure-storage --link'),
                Forms\Components\Select::make('status')->options(config('academic.student_statuses'))->required(),
                Forms\Components\TextInput::make('degree'),
                Forms\Components\TextInput::make('batch')
                    ->placeholder('e.g. Fall 2022, Batch 19')
                    ->helperText('Shown on the public students page.'),
                Forms\Components\TextInput::make('thesis_title')
                    ->label('Research / project title')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('publication_id')
                    ->label('Linked publication')
                    ->options(fn () => Publication::query()->orderByDesc('year')->orderBy('title')->pluck('title', 'id'))
                    ->searchable()
                    ->helperText('Optional. Links to a paper from your publication profile on the students page.')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description')
                    ->label('Abstract')
                    ->rows(4)
                    ->columnSpanFull(),
            ])->columns(2),
            Forms\Components\Section::make('Supervision & dates')->schema([
                Forms\Components\TextInput::make('co_supervisors'),
                Forms\Components\TextInput::make('start_year')->numeric(),
                Forms\Components\TextInput::make('completion_year')->numeric(),
                Forms\Components\DatePicker::make('completed_at')
                    ->label('Completion date')
                    ->helperText('Optional. Shown for completed students; year-only is used if left empty.'),
            ])->columns(2),
            Forms\Components\Section::make('Student profiles')->schema([
                Forms\Components\Repeater::make('profile_links')
                    ->label('Profile links')
                    ->schema([
                        Forms\Components\Select::make('platform')
                            ->options(config('academic.student_profile_platforms'))
                            ->required(),
                        Forms\Components\TextInput::make('url')
                            ->label('Profile URL')
                            ->url()
                            ->required()
                            ->placeholder('https://'),
                    ])
                    ->defaultItems(0)
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => config('academic.student_profile_platforms.'.$state['platform'], $state['platform'] ?? 'Profile link'))
                    ->columnSpanFull(),
            ]),
            Forms\Components\Section::make('Display')->schema([
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->helperText('Lower numbers appear first on the public students page.'),
                Forms\Components\Toggle::make('is_visible')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')->label('#')->sortable(),
                Tables\Columns\ImageColumn::make('photo_path')->label('Photo')->disk('public')->circular(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('degree'),
                Tables\Columns\TextColumn::make('batch'),
                Tables\Columns\TextColumn::make('thesis_title')->limit(40),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(config('academic.student_statuses')),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->headerActions([Tables\Actions\CreateAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageStudents::route('/'),
        ];
    }
}
