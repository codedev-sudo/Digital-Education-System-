<?php

namespace App\Filament\Student\Resources;

use App\Filament\Student\Resources\SessionResource\Pages;
use App\Models\Session;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SessionResource extends Resource
{
    protected static ?string $model = Session::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'My Sessions';

    public static function form(Schema $schema): Schema
    {
        // Students cannot create/edit sessions.
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $studentId = Auth::guard('student')->id();

                $query->whereHas('students', fn ($q) => $q->whereKey($studentId));
            })
            ->columns([
                Tables\Columns\TextColumn::make('start_at')
                    ->label('Start')
                    ->dateTime('Y-m-d H:i'),
                Tables\Columns\TextColumn::make('end_at')
                    ->label('End')
                    ->dateTime('Y-m-d H:i'),
                Tables\Columns\TextColumn::make('teacher.full_name')
                    ->label('Teacher')
                    ->searchable(),
                Tables\Columns\TextColumn::make('duration_hours')
                    ->label('Hours')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
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
                Tables\Filters\SelectFilter::make('teacher_id')
                    ->label('Teacher')
                    ->relationship('teacher', 'full_name'),
            ])
            ->defaultSort('start_at', 'desc')
            ->recordActions([])
            ->headerActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSessions::route('/'),
        ];
    }
}

