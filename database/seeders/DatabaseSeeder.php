<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Admin@12345'),
            ],
        );

        $teacher = Teacher::query()->firstOrCreate(
            ['unique_id' => 'TEACH001'],
            [
                'email' => 'teacher@example.com',
                'password' => Hash::make('Teacher@12345'),
                'full_name' => 'Demo Teacher',
                'gender' => 'Male',
                'date_of_join' => now()->toDateString(),
                'salary_per_hour' => 500,
            ],
        );

        $student = Student::query()->firstOrCreate(
            ['unique_id' => 'STUD001'],
            [
                'password' => Hash::make('Student@12345'),
                'full_name' => 'Demo Student',
                'parent_contact_number' => '9999999999',
                'parent_email' => 'parent@example.com',
                'dob' => '2010-01-01',
                'date_of_join' => now()->toDateString(),
                'weekly_max_hours' => 10,
                'daily_max_hours' => 2,
            ],
        );

        $teacher->students()->syncWithoutDetaching([$student->id]);
    }
}
