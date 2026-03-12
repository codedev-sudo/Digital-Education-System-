<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\SessionResource\Pages;
use App\Models\Session;
use App\Models\Student;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class SessionResource extends Resource
{
    protected static ?string $model = Session::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Sessions';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\DateTimePicker::make('start_at')
                    ->label('Session start date & time')
                    ->required()
                    ->maxDate(now())
                    ->native(false),
                Forms\Components\DateTimePicker::make('end_at')
                    ->label('Session end date & time')
                    ->required()
                    ->after('start_at')
                    ->maxDate(now())
                    ->native(false),
                Forms\Components\Select::make('students')
                    ->label('Students')
                    ->relationship(
                        name: 'students',
                        titleAttribute: 'full_name',
                        modifyQueryUsing: fn ($query) => $query->whereHas('teachers', function ($q) {
                            $q->whereKey(Auth::guard('teacher')->id());
                        })
                    )
                    ->multiple()
                    ->required()
                    ->preload()
                    ->searchable(),
                Forms\Components\Textarea::make('notes')
                    ->rows(3),
                Forms\Components\FileUpload::make('documents')
                    ->label('Session documents')
                    ->disk('public')
                    ->multiple()
                    ->directory('session-documents')
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'text/plain',
                    ])
                    ->maxSize(1024)
                    ->helperText('You can upload multiple documents, each up to 1 MB.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $query->where('teacher_id', Auth::guard('teacher')->id());
            })
            ->columns([
                Tables\Columns\TextColumn::make('start_at')->dateTime()->label('Start'),
                Tables\Columns\TextColumn::make('end_at')->dateTime()->label('End'),
                Tables\Columns\TextColumn::make('students.full_name')
                    ->label('Students')
                    ->badge()
                    ->separator(', '),
                Tables\Columns\TextColumn::make('payout_amount')
                    ->label('Payout')
                    ->money('INR'),
            ])
            ->filters([
                Tables\Filters\Filter::make('date_range')
                    ->label('Date range')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('start_at', '>=', $date))
                            ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('start_at', '<=', $date));
                    }),
                Tables\Filters\SelectFilter::make('students')
                    ->label('Student')
                    ->multiple()
                    ->relationship('students', 'full_name'),
            ])
            ->defaultSort('start_at', 'desc')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function validateBusinessRules(array $data, Session $session): void
    {
        $start = Carbon::parse($data['start_at']);
        $end = Carbon::parse($data['end_at']);

        if ($end->lessThanOrEqualTo($start)) {
            throw ValidationException::withMessages([
                'end_at' => 'Session end time must be after start time.',
            ]);
        }

        if ($start->isFuture() || $end->isFuture()) {
            throw ValidationException::withMessages([
                'start_at' => 'Session dates cannot be in the future.',
            ]);
        }

        $durationHours = $end->floatDiffInRealHours($start);

        $students = Student::query()->whereIn('id', $data['students'] ?? [])->get();

        foreach ($students as $student) {
            // Daily limit
            $dayStart = $start->copy()->startOfDay();
            $dayEnd = $start->copy()->endOfDay();

            $dailyHours = $student->sessions()
                ->whereBetween('start_at', [$dayStart, $dayEnd])
                ->get()
                ->sum(fn (Session $s) => $s->duration_hours);

            if ($dailyHours + $durationHours > (float) $student->daily_max_hours) {
                throw ValidationException::withMessages([
                    'start_at' => "Daily maximum hours exceeded for student {$student->full_name}.",
                ]);
            }

            // Weekly limit
            $weekStart = $start->copy()->startOfWeek();
            $weekEnd = $start->copy()->endOfWeek();

            $weeklyHours = $student->sessions()
                ->whereBetween('start_at', [$weekStart, $weekEnd])
                ->get()
                ->sum(fn (Session $s) => $s->duration_hours);

            if ($weeklyHours + $durationHours > (float) $student->weekly_max_hours) {
                throw ValidationException::withMessages([
                    'start_at' => "Weekly maximum hours exceeded for student {$student->full_name}.",
                ]);
            }

            // Overlap check
            $overlap = $student->sessions()
                ->where(function ($q) use ($start, $end) {
                    $q->whereBetween('start_at', [$start, $end])
                        ->orWhereBetween('end_at', [$start, $end])
                        ->orWhere(function ($q2) use ($start, $end) {
                            $q2->where('start_at', '<=', $start)
                               ->where('end_at', '>=', $end);
                        });
                })
                ->when($session->exists, fn ($q) => $q->where('id', '!=', $session->id))
                ->exists();

            if ($overlap) {
                throw ValidationException::withMessages([
                    'start_at' => "Session time overlaps with an existing session for student {$student->full_name}.",
                ]);
            }
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSessions::route('/'),
            'create' => Pages\CreateSession::route('/create'),
            'edit' => Pages\EditSession::route('/{record}/edit'),
        ];
    }
}

