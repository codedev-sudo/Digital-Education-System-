<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id', 12)->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('full_name');
            $table->string('gender');
            $table->date('date_of_join');
            $table->decimal('salary_per_hour', 10, 2);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};

