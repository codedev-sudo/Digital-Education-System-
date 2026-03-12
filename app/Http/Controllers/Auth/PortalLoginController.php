<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PortalLoginController extends Controller
{
    public function show()
    {
        return view('auth.portal-login');
    }

    public function authenticate(Request $request)
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(['teacher', 'student'])],
            'unique_id' => ['required', 'string', 'min:6', 'max:12', 'regex:/^[a-zA-Z0-9]+$/'],
            'password' => ['required', 'string'],
        ], [
            'unique_id.min' => 'Unique ID must be at least 6 characters.',
            'unique_id.max' => 'Unique ID must be at most 12 characters.',
            'unique_id.regex' => 'Unique ID must contain only letters and numbers.',
        ]);

        $guard = $validated['role'];
        $model = $guard === 'teacher' ? Teacher::class : Student::class;

        $user = $model::query()->where('unique_id', $validated['unique_id'])->first();

        if (! $user || ! Auth::guard($guard)->attempt(['unique_id' => $validated['unique_id'], 'password' => $validated['password']], $request->boolean('remember'))) {
            return back()
                ->withErrors(['unique_id' => 'Invalid credentials.'])
                ->onlyInput('unique_id', 'role');
        }

        $request->session()->regenerate();

        return redirect()->intended($guard === 'teacher' ? '/teacher' : '/student');
    }

    public function logout(Request $request)
    {
        $role = $request->input('role');

        if (in_array($role, ['teacher', 'student'], true)) {
            Auth::guard($role)->logout();
        } else {
            Auth::guard('teacher')->logout();
            Auth::guard('student')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}

