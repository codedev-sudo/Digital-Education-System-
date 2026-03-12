<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherResource\Pages;
use App\Models\Teacher;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Password;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;

class TeacherResource extends Resource
{
    protected static ?string $model = Teacher::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('unique_id')
                    ->label('Unique ID')
                    ->required()
                    ->minLength(6)
                    ->maxLength(12)
                    ->regex('/^[a-zA-Z0-9]+$/')
                    ->validationMessages([
                        'min' => 'Unique ID must be at least 6 characters.',
                        'max' => 'Unique ID must be at most 12 characters.',
                        'regex' => 'Unique ID must contain only letters and numbers.',
                    ])
                    ->helperText('Alphanumeric only, 6–12 characters.')
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->visibleOn(Operation::Create)
                    ->rule(Password::defaults())
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn (?string $state): bool => filled($state)),
                Forms\Components\TextInput::make('full_name')->required(),
                Forms\Components\Select::make('gender')
                    ->required()
                    ->options([
                        'Male' => 'Male',
                        'Female' => 'Female',
                        'Other' => 'Other',
                    ]),
                Forms\Components\DatePicker::make('date_of_join')->required(),
                Forms\Components\TextInput::make('salary_per_hour')
                    ->numeric()
                    ->required(),
                Forms\Components\CheckboxList::make('students')
                    ->relationship('students', 'full_name')
                    ->columns(2)
                    ->searchable()
                    ->bulkToggleable()
                    ->helperText('Assign one or more students to this teacher.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('unique_id')->searchable(),
                Tables\Columns\TextColumn::make('full_name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('gender'),
                Tables\Columns\TextColumn::make('date_of_join')->date(),
                Tables\Columns\TextColumn::make('salary_per_hour')->money('INR'),
                Tables\Columns\TextColumn::make('students_count')->counts('students')->label('Students'),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('delete')
                        ->label('Delete selected')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->delete()),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeachers::route('/'),
            'create' => Pages\CreateTeacher::route('/create'),
            'edit' => Pages\EditTeacher::route('/{record}/edit'),
        ];
    }
}

