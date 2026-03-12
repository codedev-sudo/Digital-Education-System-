<?php

namespace App\Filament\Teacher\Widgets;

use App\Models\Session;
use App\Models\Teacher;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class TeacherStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        /** @var Teacher $teacher */
        $teacher = Auth::guard('teacher')->user();

        if (! $teacher) {
            return [];
        }

        $today = now()->toDateString();
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        $todaySessions = $teacher->sessions()
            ->whereDate('start_at', $today)
            ->count();

        $weekHours = $teacher->sessions()
            ->whereBetween('start_at', [$weekStart, $weekEnd])
            ->get()
            ->sum(fn (Session $s) => $s->duration_hours);

        $studentCount = $teacher->students()->count();

        return [
            Stat::make('My Students', $studentCount),
            Stat::make('Today\'s Sessions', $todaySessions),
            Stat::make('Hours this week', number_format((float) $weekHours, 2)),
        ];
    }
}

