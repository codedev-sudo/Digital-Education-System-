<?php

namespace App\Models;

use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Student extends Authenticatable implements HasName
{
    use HasFactory, Notifiable;

    public function getFilamentName(): string
    {
        return (string) $this->getAttributeValue('full_name');
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class)->withTimestamps();
    }

    public function sessions()
    {
        return $this->belongsToMany(Session::class, 'class_session_student')->withTimestamps();
    }

    protected $fillable = [
        'unique_id',
        'password',
        'full_name',
        'parent_contact_number',
        'parent_email',
        'dob',
        'date_of_join',
        'weekly_max_hours',
        'daily_max_hours',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'dob' => 'date',
            'date_of_join' => 'date',
            'weekly_max_hours' => 'decimal:2',
            'daily_max_hours' => 'decimal:2',
        ];
    }
}

