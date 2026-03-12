<?php

namespace App\Filament\Teacher\Resources\SessionResource\Pages;

use App\Filament\Teacher\Resources\SessionResource;
use App\Models\Session;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateSession extends CreateRecord
{
    protected static string $resource = SessionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $teacherId = Auth::guard('teacher')->id();
        $data['teacher_id'] = $teacherId;

        SessionResource::validateBusinessRules($data, new Session());

        // compute payout based on duration * teacher salary
        $teacher = Auth::guard('teacher')->user();
        $start = new \Carbon\Carbon($data['start_at']);
        $end = new \Carbon\Carbon($data['end_at']);
        $hours = $start->diffInMinutes($end) / 60;
        $data['payout_amount'] = $hours * (float) $teacher->salary_per_hour;

        return $data;
    }
}

