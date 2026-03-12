<?php

namespace App\Filament\Student\Widgets;

use App\Models\Session;
use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StudentStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        /** @var Student $student */
        $student = Auth::guard('student')->user();

        if (! $student) {
            return [];
        }

        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $sessionCount = $student->sessions()
            ->whereBetween('start_at', [$monthStart, $monthEnd])
            ->count();

        $hours = $student->sessions()
            ->whereBetween('start_at', [$monthStart, $monthEnd])
            ->get()
            ->sum(fn (Session $s) => $s->duration_hours);

        $teacherCount = $student->teachers()->count();

        return [
            Stat::make('My Teachers', $teacherCount),
            Stat::make('Sessions this month', $sessionCount),
            Stat::make('Hours this month', number_format((float) $hours, 2)),
        ];
    }
}

