<?php

namespace App\Filament\Widgets;

use App\Models\Session;
use App\Models\Student;
use App\Models\Teacher;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Teachers', Teacher::count()),
            Stat::make('Total Students', Student::count()),
            Stat::make('Total Sessions', Session::count()),
        ];
    }
}

