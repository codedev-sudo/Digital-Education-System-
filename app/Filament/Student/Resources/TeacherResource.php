<?php

namespace App\Filament\Student\Resources;

use App\Filament\Student\Resources\TeacherResource\Pages;
use App\Models\Teacher;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TeacherResource extends Resource
{
    protected static ?string $model = Teacher::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'My Teachers';

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $studentId = Auth::guard('student')->id();

                $query->whereHas('students', fn ($q) => $q->whereKey($studentId));
            })
            ->columns([
                Tables\Columns\TextColumn::make('unique_id')->label('ID'),
                Tables\Columns\TextColumn::make('full_name')->label('Name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('gender'),
                Tables\Columns\TextColumn::make('date_of_join')->label('Date of join')->date(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gender')
                    ->options([
                        'Male' => 'Male',
                        'Female' => 'Female',
                        'Other' => 'Other',
                    ]),
                Tables\Filters\Filter::make('joined_between')
                    ->label('Date of join')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from'),
                        \Filament\Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('date_of_join', '>=', $date))
                            ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('date_of_join', '<=', $date));
                    }),
            ])
            ->defaultSort('full_name')
            ->recordActions([])
            ->headerActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeachers::route('/'),
        ];
    }
}

