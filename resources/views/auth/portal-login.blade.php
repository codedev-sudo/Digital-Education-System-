<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 mb-1">Portal Login</h1>
                    <p class="text-muted mb-4">Teacher & Student login using Unique ID.</p>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('portal.login') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select" required>
                                <option value="teacher" @selected(old('role', 'teacher') === 'teacher')>Teacher</option>
                                <option value="student" @selected(old('role') === 'student')>Student</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Unique ID</label>
                            <input name="unique_id" value="{{ old('unique_id') }}" class="form-control" minlength="6" maxlength="12" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input name="password" type="password" class="form-control" required>
                        </div>

                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>

                    <hr class="my-4">

                    <div class="small text-muted">
                        Admin login is separate at <a href="/admin/login">/admin/login</a>.
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>

