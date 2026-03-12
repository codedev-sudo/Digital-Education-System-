<?php

namespace App\Models;

use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Teacher extends Authenticatable implements HasName
{
    use HasFactory, Notifiable;

    public function getFilamentName(): string
    {
        return (string) $this->getAttributeValue('full_name');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class)->withTimestamps();
    }

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    protected $fillable = [
        'unique_id',
        'email',
        'password',
        'full_name',
        'gender',
        'date_of_join',
        'salary_per_hour',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'date_of_join' => 'date',
            'salary_per_hour' => 'decimal:2',
        ];
    }
}

