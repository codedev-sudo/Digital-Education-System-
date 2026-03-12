<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SessionResource\Pages;
use App\Models\Session as SessionModel;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class SessionResource extends Resource
{
    protected static ?string $model = SessionModel::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Sessions';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    public static function form(Schema $schema): Schema
    {
        // Admin does not create sessions here; form not used.
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('start_at')
                    ->label('Start')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_at')
                    ->label('End')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('teacher.full_name')
                    ->label('Teacher')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('students.full_name')
                    ->label('Students')
                    ->badge()
                    ->separator(', ')
                    ->searchable(),
                Tables\Columns\TextColumn::make('duration_hours')
                    ->label('Hours')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                Tables\Columns\TextColumn::make('payout_amount')
                    ->label('Payout')
                    ->money('INR')
                    ->sortable(),
            ])
            ->defaultSort('start_at', 'desc')
            ->filters([
                Filter::make('date_range')
                    ->label('Date range')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('start_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('start_at', '<=', $date));
                    }),
                SelectFilter::make('teacher_id')
                    ->label('Teacher')
                    ->relationship('teacher', 'full_name'),
                SelectFilter::make('students')
                    ->label('Student')
                    ->multiple()
                    ->relationship('students', 'full_name'),
            ])
            ->recordActions([])
            ->headerActions([])
            ->emptyStateHeading('No sessions found');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSessions::route('/'),
        ];
    }
}

