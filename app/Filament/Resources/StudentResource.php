<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Models\Student;
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
use Filament\Actions\Action;
use Illuminate\Support\Facades\Mail;
use App\Mail\MonthlySessionReportMail;
use Carbon\Carbon;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user';

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
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->visibleOn(Operation::Create)
                    ->rule(Password::defaults())
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn (?string $state): bool => filled($state)),
                Forms\Components\TextInput::make('full_name')->required(),
                Forms\Components\TextInput::make('parent_contact_number')->required(),
                Forms\Components\TextInput::make('parent_email')->email()->required(),
                Forms\Components\DatePicker::make('dob')->required(),
                Forms\Components\DatePicker::make('date_of_join')->required(),
                Forms\Components\TextInput::make('weekly_max_hours')->numeric()->required(),
                Forms\Components\TextInput::make('daily_max_hours')->numeric()->required(),
                Forms\Components\CheckboxList::make('teachers')
                    ->relationship('teachers', 'full_name')
                    ->columns(2)
                    ->searchable()
                    ->bulkToggleable()
                    ->helperText('Assign one or more teachers to this student.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('unique_id')->searchable(),
                Tables\Columns\TextColumn::make('full_name')->searchable(),
                Tables\Columns\TextColumn::make('parent_email')->searchable(),
                Tables\Columns\TextColumn::make('parent_contact_number'),
                Tables\Columns\TextColumn::make('dob')->date(),
                Tables\Columns\TextColumn::make('date_of_join')->date(),
                Tables\Columns\TextColumn::make('weekly_max_hours'),
                Tables\Columns\TextColumn::make('daily_max_hours'),
                Tables\Columns\TextColumn::make('teachers_count')->counts('teachers')->label('Teachers'),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                Action::make('sendMonthlyReport')
                    ->label('Send monthly report')
                    ->icon('heroicon-o-paper-airplane')
                    ->form([
                        Forms\Components\Select::make('month')
                            ->label('Month')
                            ->required()
                            ->options([
                                1 => 'January',
                                2 => 'February',
                                3 => 'March',
                                4 => 'April',
                                5 => 'May',
                                6 => 'June',
                                7 => 'July',
                                8 => 'August',
                                9 => 'September',
                                10 => 'October',
                                11 => 'November',
                                12 => 'December',
                            ])
                            ->default(now()->month),
                        Forms\Components\TextInput::make('year')
                            ->numeric()
                            ->required()
                            ->default(now()->year),
                    ])
                    ->action(function (Student $record, array $data): void {
                        $year = (int) $data['year'];
                        $month = (int) $data['month'];

                        $start = Carbon::create($year, $month, 1)->startOfMonth();
                        $end = $start->copy()->endOfMonth();

                        $sessions = $record->sessions()
                            ->with('teacher')
                            ->whereBetween('start_at', [$start, $end])
                            ->orderBy('start_at')
                            ->get();

                        Mail::to($record->parent_email)->send(
                            new MonthlySessionReportMail($record, $sessions, $year, $month)
                        );
                    })
                    ->visible(fn (Student $record): bool => filled($record->parent_email)),
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
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}

