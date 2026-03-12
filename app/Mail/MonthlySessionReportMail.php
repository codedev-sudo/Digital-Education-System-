<?php

namespace App\Mail;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class MonthlySessionReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Student $student,
        public Collection $sessions,
        public int $year,
        public int $month,
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject('Monthly Session Report')
            ->view('emails.monthly-session-report');
    }
}

