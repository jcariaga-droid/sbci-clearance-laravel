<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Clearance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    private const STUDENT_PROGRAMS = [
        'BEEd – GenEd',
        'BSEd – Math',
        'BSHM',
        'BSCA',
        'BSIT',
        'BSTM',
        'BSEntrep',
        'BSCrim',
    ];

    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    // STUDENT REGISTRATION
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'program' => [
                'required',
                'string',
                Rule::in(self::STUDENT_PROGRAMS),
            ],
            'password' => 'required|string|min:8|confirmed',
        ]);

        // ONLY SBCI EMAIL DOMAIN IS ALLOWED
        $email = trim($validated['email']);

        $emailDomain = strtolower(
            substr(strrchr($email, '@') ?: '', 1)
        );

        if ($emailDomain !== 'subicbaycolleges.com') {
            return back()->withErrors([
                'email' => 'Only @subicbaycolleges.com email addresses are allowed to register.',
            ])->withInput();
        }

        // CHECK EXISTING ACCOUNT
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            return back()->withErrors([
                'email' => 'The email has already been taken.',
            ])->withInput();
        }

        // CREATE STUDENT ACCOUNT
        $user = User::create([
            'name' => $validated['name'],
            'email' => $email,
            'password' => Hash::make($validated['password']),
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        // SAVE STUDENT PROGRAM
        // Assign separately to avoid mass-assignment issues.
        $user->program = $validated['program'];
        $user->save();

        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Account created successfully!');
    }

    // LOGIN
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // ADMIN
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            // STAFF
            if ($user->role === 'staff') {
                return redirect()->route('staff.clearances');
            }

            // STUDENT
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid email or password.',
        ])->onlyInput('email');
    }

    // LOGOUT
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // DASHBOARD
    public function dashboard()
    {
        $user = Auth::user();

        if ($user->role === 'staff') {
            return redirect()->route('staff.clearances');
        }

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        $clearances = Clearance::where(
            'student_id',
            $user->email
        )->get();

        return view('dashboard', compact('user', 'clearances'));
    }
}
