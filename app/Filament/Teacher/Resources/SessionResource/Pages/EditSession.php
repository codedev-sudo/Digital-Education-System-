<?php

namespace App\Filament\Teacher\Resources\SessionResource\Pages;

use App\Filament\Teacher\Resources\SessionResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditSession extends EditRecord
{
    protected static string $resource = SessionResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        static::getResource()::validateBusinessRules($data, $this->record);

        $teacher = Auth::guard('teacher')->user();
        $start = new \Carbon\Carbon($data['start_at']);
        $end = new \Carbon\Carbon($data['end_at']);
        $hours = $start->diffInMinutes($end) / 60;
        $data['payout_amount'] = $hours * (float) $teacher->salary_per_hour;

        return $data;
    }
}

