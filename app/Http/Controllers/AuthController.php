<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Clearance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
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
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // ONLY SBCI EMAIL DOMAIN IS ALLOWED
        $email = trim($request->email);

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

        // CREATE STUDENT ACCOUNT WITHOUT EMAIL VERIFICATION
        $user = User::create([
            'name' => $request->name,
            'email' => $email,
            'password' => Hash::make($request->password),
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

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
