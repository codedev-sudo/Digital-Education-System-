<?php

namespace App\Models;

use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    protected $table = 'class_sessions';

    protected $fillable = [
        'teacher_id',
        'start_at',
        'end_at',
        'notes',
        'documents',
        'payout_amount',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'documents' => 'array',
        'payout_amount' => 'decimal:2',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'class_session_student')->withTimestamps();
    }

    public function getDurationHoursAttribute(): float
    {
        if (! $this->start_at || ! $this->end_at) {
            return 0.0;
        }

        return CarbonInterval::seconds($this->end_at->diffInSeconds($this->start_at))->totalHours;
    }
}

