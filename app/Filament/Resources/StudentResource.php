<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
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
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\Select::make('status')->options(config('academic.student_statuses'))->required(),
            Forms\Components\TextInput::make('degree'),
            Forms\Components\TextInput::make('thesis_title')->required()->columnSpanFull(),
            Forms\Components\TextInput::make('co_supervisors'),
            Forms\Components\TextInput::make('start_year')->numeric(),
            Forms\Components\TextInput::make('completion_year')->numeric(),
            Forms\Components\Textarea::make('description')->rows(3)->columnSpanFull(),
            Forms\Components\Toggle::make('is_visible')->default(true),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('degree'),
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
