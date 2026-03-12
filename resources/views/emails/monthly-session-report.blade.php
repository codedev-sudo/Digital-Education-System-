@php
    $monthName = \Carbon\Carbon::create($year, $month, 1)->format('F Y');
@endphp

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Monthly Session Report - {{ $monthName }}</title>
</head>
<body>
    <h2>Monthly Session Report for {{ $student->full_name }} ({{ $monthName }})</h2>

    <p>Dear Parent,</p>

    <p>Below is the summary of sessions for your child during {{ $monthName }}.</p>

    @if ($sessions->isEmpty())
        <p>No sessions were recorded for this month.</p>
    @else
        <table border="1" cellpadding="6" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th align="left">Date</th>
                    <th align="left">Start</th>
                    <th align="left">End</th>
                    <th align="left">Teacher</th>
                    <th align="right">Hours</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalHours = 0;
                @endphp
                @foreach ($sessions as $session)
                    @php
                        $hours = $session->duration_hours;
                        $totalHours += $hours;
                    @endphp
                    <tr>
                        <td>{{ $session->start_at->format('Y-m-d') }}</td>
                        <td>{{ $session->start_at->format('H:i') }}</td>
                        <td>{{ $session->end_at->format('H:i') }}</td>
                        <td>{{ $session->teacher?->full_name ?? '-' }}</td>
                        <td align="right">{{ number_format($hours, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" align="right">Total Hours</th>
                    <th align="right">{{ number_format($totalHours, 2) }}</th>
                </tr>
            </tfoot>
        </table>
    @endif

    <p>Regards,<br>{{ config('app.name') }}</p>
</body>
</html>

