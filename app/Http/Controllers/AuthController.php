<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Clearance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

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
            throw ValidationException::withMessages([
                'email' => 'Only @subicbaycolleges.com email addresses are allowed to register.',
            ]);
        }

        // CHECK EXISTING ACTIVE ACCOUNT
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            throw ValidationException::withMessages([
                'email' => 'The email has already been taken.',
            ]);
        }

        // CREATE STUDENT ACCOUNT
        $user = User::create([
            'name' => $request->name,
            'email' => $email,
            'password' => Hash::make($request->password),
            'role' => 'student',
        ]);

        Auth::login($user);

        // TRY TO SEND VERIFICATION EMAIL
        $emailSent = $this->sendVerificationEmail($user);

        if ($emailSent) {
            return redirect()->route('verification.notice')
                ->with('success', 'Account created! Please check your email for verification.');
        }

        return redirect()->route('verification.notice')
            ->with('warning', 'Your account was created, but the verification email could not be sent. Please try again later.');
    }

    // SEND VERIFICATION EMAIL
    private function sendVerificationEmail($user)
    {
        try {
            $link = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'id' => (string) $user->_id,
                    'hash' => sha1($user->email),
                ]
            );

            Mail::raw(
                "Hello {$user->name},\n\n"
                . "Thank you for registering in the SBCI Clearance System.\n\n"
                . "Please click the link below to verify your email address:\n\n"
                . $link . "\n\n"
                . "This link will expire in 60 minutes.\n\n"
                . "If you did not create this account, please ignore this email.",
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('SBCI Clearance System - Verify Your Email');
                }
            );

            return true;

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error(
                'SBCI verification email failed: ' . $e->getMessage(),
                ['user_id' => (string) $user->_id]
            );

            return false;
        }
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

            // STUDENT EMAIL VERIFICATION
            if (
                $user->role === 'student' &&
                empty($user->email_verified_at)
            ) {
                $emailSent = $this->sendVerificationEmail($user);

                if ($emailSent) {
                    return redirect()->route('verification.notice')
                        ->with('success', 'Please verify your email. A new verification link has been sent.');
                }

                return redirect()->route('verification.notice')
                    ->with('warning', 'You are not verified yet. The verification email could not be sent. Please try again later.');
            }

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
